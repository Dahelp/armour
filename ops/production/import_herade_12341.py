"""Import only HERADE article 12341 from the supplied document over pinned FTPS."""
import base64
import html
import json
import os
from pathlib import Path
import re
import secrets
import subprocess
import tempfile
import urllib.request
from deploy_ftps import ROOT, PIN

source_data = json.loads((ROOT / 'resources/products/herade-12341.json').read_text(encoding='utf-8'))
apply = os.environ.get('APPLY', 'false') == 'true'
name = '__herade_' + secrets.token_hex(16) + '.php'
token = secrets.token_hex(32)
encoded = base64.b64encode(json.dumps(source_data, ensure_ascii=False).encode()).decode()
body = (ROOT / 'bin/import_herade_12341.php').read_text(encoding='utf-8').removeprefix('<?php').replace('declare(strict_types=1);', '', 1)
runner_source = "<?php\nheader('Content-Type: application/json; charset=utf-8');\nheader('Cache-Control: no-store');\nif ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals('" + token + "', (string)($_SERVER['HTTP_X_RUNNER_TOKEN'] ?? ''))) { http_response_code(403); exit; }\n$heradeSource=json_decode(base64_decode('" + encoded + "'),true,512,JSON_THROW_ON_ERROR);\n$heradeApply=" + ('true' if apply else 'false') + ";\n" + body
connection = ['curl', '--fail', '--silent', '--show-error', '--ssl-reqd', '--tlsv1.2', '--cacert', str(ROOT / 'ops/production/grass-ftps.pem'), '--pinnedpubkey', PIN, '--ciphers', 'DEFAULT:@SECLEVEL=1', '--connect-timeout', '20', '--max-time', '120', '--user', os.environ['FTP_USERNAME'] + ':' + os.environ['FTP_PASSWORD']]

def transfer(options):
    if subprocess.run(connection + options, cwd=ROOT).returncode:
        raise RuntimeError('Pinned FTPS transfer failed')

def clean(text):
    return ' '.join(html.unescape(re.sub('<[^>]*>', '', text)).split())

with tempfile.TemporaryDirectory() as directory:
    runner = Path(directory) / name
    runner.write_text(runner_source, encoding='utf-8')
    subprocess.run(['php', '-l', str(runner)], check=True)
    try:
        transfer(['--upload-file', str(runner), 'ftp://grass.gohost.ru:21//public/' + name])
        request = urllib.request.Request('https://techtires.ru/' + name, data=b'', headers={'X-Runner-Token': token})
        try:
            response = urllib.request.urlopen(request, timeout=90)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            result = json.load(response)
        (ROOT / 'herade-result.json').write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding='utf-8')
        print(json.dumps(result, ensure_ascii=False))
        if result.get('ok') is not True:
            raise RuntimeError('Product import failed')
        if apply:
            with urllib.request.urlopen('https://techtires.ru/' + source_data['alias'], timeout=60) as response:
                page = response.read().decode('utf-8')
            title = re.search(r'<title>(.*?)</title>', page, re.S).group(1)
            description = re.search(r'<meta name="description" content="(.*?)"', page, re.S).group(1)
            if html.unescape(title) != source_data['title'] or html.unescape(description) != source_data['description']:
                raise RuntimeError('Public metadata differs from the document')
            for paragraph in source_data['paragraphs']:
                if paragraph not in html.unescape(page):
                    raise RuntimeError('Full document description was not published')
            schema = re.search(r'<script type="application/ld\+json">(\{.*?"@type":"Product".*?\})</script>', page, re.S)
            product_schema = json.loads(schema.group(1))
            offer = product_schema['offers']
            if float(offer['price']) != float(source_data['price']) or offer['availability'] != ('https://schema.org/InStock' if source_data['quantity'] > 0 else 'https://schema.org/OutOfStock'):
                raise RuntimeError('Public price or availability differs from requested values')
            if source_data['quantity'] > 0 and 'data-max="' + str(source_data['quantity']) + '"' not in page:
                raise RuntimeError('Public purchase quantity limit differs from requested stock')
            rows = re.findall(r'<th class="attribute_name 1">(.*?)</th><td class="attribute_value">(.*?)</td>', page, re.S)
            attributes = {clean(key).lower(): clean(value) for key, value in rows}
            if len(attributes) != 11:
                raise RuntimeError('Public card must show all eleven document characteristics')
            for attribute in source_data['attributes']:
                if attributes.get(attribute['name'].lower()) != attribute['value']:
                    raise RuntimeError('Incorrect public characteristic: ' + attribute['name'])
            if '/images/product/baseimg/' + source_data['image'] not in page or 'href="https://techtires.ru/' + source_data['category_alias'] + '"' not in page:
                raise RuntimeError('Wrong public image or category')
            with urllib.request.urlopen('https://techtires.ru/images/product/baseimg/' + source_data['image'], timeout=60) as response:
                if response.status != 200:
                    raise RuntimeError('Donor photo is unavailable')
            print('Verified document metadata, five full paragraphs, eleven characteristics, category and donor photo')
    finally:
        transfer(['--quote', 'DELE /public/' + name, 'ftp://grass.gohost.ru:21//'])