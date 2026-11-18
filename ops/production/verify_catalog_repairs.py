"""Verify product completeness and every registered public filter's canonical/legacy URLs."""
from concurrent.futures import ThreadPoolExecutor
import html
import json
import re
import urllib.error
import urllib.parse
import urllib.request

BASE = 'https://techtires.ru'
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None

def fetch(path, method='GET'):
    url = BASE + '/' + urllib.parse.quote(path.lstrip('/'), safe='/?=&.%*-')
    opener = urllib.request.build_opener(NoRedirect())
    try:
        response = opener.open(urllib.request.Request(url, method=method), timeout=40)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        return response.status, response.headers.get('Location'), response.read().decode('utf-8', errors='replace')

def clean(text):
    return ' '.join(html.unescape(re.sub('<[^>]*>', '', text)).split())

def require(ok, message):
    if not ok:
        raise RuntimeError(message)

def verify(result, sources):
    product_checks = []
    by_article = {product['article']: product for product in result['products']}
    for source in sources:
        status, _, page = fetch(source['alias'])
        require(status == 200, 'Product page failed: ' + source['article'])
        require(html.unescape(re.search(r'<title>(.*?)</title>', page, re.S).group(1)) == source['title'], 'Product title differs from document')
        require(html.unescape(re.search(r'<meta name="description" content="(.*?)"', page, re.S).group(1)) == source['description'], 'Product description metadata differs')
        schema = re.search(r'<script type="application/ld\+json">(\{.*?"@type":"Product".*?\})</script>', page, re.S)
        offer = json.loads(schema.group(1))['offers']
        require(float(offer['price']) == float(source['price']), 'Product price differs from confirmed source')
        require(offer['availability'] == ('https://schema.org/InStock' if source['quantity'] > 0 else 'https://schema.org/OutOfStock'), 'Product stock status differs')
        if source['quantity'] > 0:
            require('data-max="' + str(source['quantity']) + '"' in page, 'Product purchase quantity differs')
        expected_image = source.get('image', 'vulcan-10302-no-photo.svg')
        require('/images/product/baseimg/' + expected_image in page, 'Product photo changed')
        description = re.search(r'<div[^>]*id="tab-description"[^>]*>(.*?)</div>', page, re.S).group(1)
        for paragraph in source['paragraphs']:
            require(paragraph in html.unescape(description), 'Document paragraph is missing')
        bullets = re.findall(r'<li[^>]*>(.*?)</li>', description, re.S)
        require(len(bullets) == len(source.get('list_items', [])), 'Document list structure was lost')
        for item, index in zip(bullets, source.get('list_items', [])):
            require(clean(item) == source['paragraphs'][index], 'List item text changed')
        technical = {clean(key).lower(): clean(value) for key, value in re.findall(r'<th class="attribute_name 1">(.*?)</th><td class="attribute_value">(.*?)</td>', page, re.S)}
        require(len(technical) == len(source['attributes']), 'Technical characteristic count differs')
        for attribute in source['attributes']:
            require(technical.get(attribute['name'].lower()) == attribute['value'], 'Technical characteristic differs: ' + attribute['name'])
        short = re.search(r'<div class="group_attr">(.*?)</div>', page, re.S).group(1)
        displayed = {clean(value) for _, value in re.findall(r'<tr>\s*<td[^>]*>(.*?)</td>\s*<td[^>]*>(.*?)</td>', short, re.S)}
        bound = by_article[source['article']]['filters']
        for facet in bound:
            if facet['alias']:
                require(facet['value'] in displayed, 'Short product characteristic is missing: ' + facet['group'])
        ids = ','.join(str(facet['id']) for facet in bound)
        category = source.get('category_alias', 'catalog-kvadrotciklov')
        status, _, filtered = fetch(category + '?filter=' + ids)
        product_links = {urllib.parse.urljoin(BASE + '/', html.unescape(link)) for link in re.findall(r'href="([^"]+)"', filtered)}
        require(status == 200 and BASE + '/' + source['alias'] in product_links, 'Product missing from category filter intersection')
        for facet in bound:
            require(re.search(r'<option\s+value="' + str(facet['id']) + r'"[^>]*>', filtered) is not None, 'Required category filter option is missing or stale: ' + facet['group'])
        size = next(facet for facet in bound if facet['group'] == 'tiposize')
        status, _, size_page = fetch(size['alias'])
        require(status == 200 and '/' + source['alias'] in size_page, 'Product missing from its size listing')
        product_checks.append({'article': source['article'], 'technical_attributes': len(technical), 'list_items': len(bullets), 'filters': len(bound)})

    pages = {row['sef']: row for row in result['filter_pages']}
    def check_filter(item):
        alias, row = item
        try:
            status, _, page = fetch(alias)
            require(status == 200, 'Canonical status ' + str(status))
            canonical = html.unescape(re.search(r'<link rel="canonical" href="(.*?)"', page).group(1))
            require(canonical == BASE + '/' + alias, 'Incorrect canonical: ' + canonical)
            for variant in (alias + '.html', alias.upper() + '.HTML', row['url_params'] + '/' + row['value']):
                status, location, _ = fetch(variant, 'HEAD')
                require(status == 301 and location == BASE + '/' + alias, 'Invalid legacy redirect: ' + variant + ' -> ' + str(status) + ' ' + str(location))
            return None
        except Exception as error:
            return {'alias': alias, 'error': str(error)}
    with ThreadPoolExecutor(max_workers=4) as pool:
        failures = [failure for failure in pool.map(check_filter, pages.items()) if failure]
    require(not failures, 'Filter URL audit failed: ' + json.dumps(failures, ensure_ascii=False))
    for path in ('tiposize-11.00-20.html', 'marka-EKKA.html'):
        status, location, _ = fetch(path + '?utm_source=legacy&page=2', 'HEAD')
        target = 'tiposize-11.00-20' if path.startswith('tiposize') else 'marka-ekka'
        require(status == 301 and location == BASE + '/' + target + '?utm_source=legacy&page=2', 'Query string was not preserved')
    for prefix in ('tiposize', 'marka', 'protect', 'diameter', 'disk'):
        status, _, page = fetch(prefix)
        require(status == 200, 'Filter index unavailable: ' + prefix)
        require('href="' + prefix + '/' not in page, 'Filter index generates noncanonical links')
    for path in ('tiposize-does-not-exist.html', 'marka-not-a-real-brand.html'):
        require(fetch(path, 'HEAD')[0] == 404, 'Unknown filter was masked by a redirect')
    status, _, sitemap = fetch('sitemap.xml')
    require(status == 200 and '.html</loc>' not in sitemap, 'Legacy HTML URLs remain in sitemap')
    sitemap_urls = {html.unescape(url) for url in re.findall(r'<loc>(.*?)</loc>', sitemap)}
    for alias in pages:
        require(BASE + '/' + alias in sitemap_urls, 'Canonical filter absent from sitemap: ' + alias)
    for url in sitemap_urls:
        require(not re.search(r'/(tiposize|marka|protect|diameter|disk)/', url), 'Technical filter path remains in sitemap: ' + url)
    return {'ok': True, 'products': product_checks, 'canonical_filters': len(pages), 'legacy_redirect_variants': 3 * len(pages), 'sitemap_urls': len(sitemap_urls), 'failures': failures}
