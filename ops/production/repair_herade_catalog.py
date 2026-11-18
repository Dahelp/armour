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
import time
import urllib.request
from deploy_ftps import ROOT, PIN

source_data = json.loads((ROOT / 'resources/products/herade-12341.json').read_text(encoding='utf-8'))
apply = os.environ.get('APPLY', 'false') == 'true'
name = '__herade_' + secrets.token_hex(16) + '.php'
token = secrets.token_hex(32)
encoded = base64.b64encode(json.dumps(source_data, ensure_ascii=False).encode()).decode()
all_sources = [json.loads((ROOT / ('resources/products/' + name + '.json')).read_text(encoding='utf-8')) for name in ('herade-12341', 'vulcan-10302')]
catalog_encoded = base64.b64encode(json.dumps(all_sources, ensure_ascii=False).encode()).decode()
body = (ROOT / 'bin/repair_herade_catalog.php').read_text(encoding='utf-8').removeprefix('<?php').replace('declare(strict_types=1);', '', 1)
runner_source = "<?php\nheader('Content-Type: application/json; charset=utf-8');\nheader('Cache-Control: no-store');\nif ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals('" + token + "', (string)($_SERVER['HTTP_X_RUNNER_TOKEN'] ?? ''))) { http_response_code(403); exit; }\n$heradeSource=json_decode(base64_decode('" + encoded + "'),true,512,JSON_THROW_ON_ERROR);\n$heradeApply=" + ('true' if apply else 'false') + ";\n" + "$catalogSources=json_decode(base64_decode('" + catalog_encoded + "'),true,512,JSON_THROW_ON_ERROR);\n" + "if (function_exists('opcache_invalidate')) { foreach (['app/services/FilterUrlCanonicalizer.php','app/services/DocumentProductContent.php','app/services/ProductDocumentFacets.php','app/services/SitemapGenerator.php','config/routes.php','app/views/armour/Tiposize/index.php','app/views/armour/Marka/index.php','app/views/armour/Protect/index.php','app/views/armour/Diameter/index.php','app/views/armour/Disk/index.php'] as $file) opcache_invalidate(dirname(__DIR__).'/'.$file,true); }\ntry {\n" + body + "\n} catch (Throwable $error) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$error->getMessage()]); }\n"
helper_payload = base64.b64encode((ROOT / 'app/services/ProductDocumentFacets.php').read_bytes()).decode()
install_helper = "$target=dirname(__DIR__).'/app/services/ProductDocumentFacets.php';$payload=base64_decode('" + helper_payload + "');$temporary=$target.'.catalog-'.bin2hex(random_bytes(6));if(file_put_contents($temporary,$payload,LOCK_EX)!==strlen($payload)||!rename($temporary,$target)){if(is_file($temporary))unlink($temporary);http_response_code(500);echo json_encode(['ok'=>false,'error'=>'Unable to publish the facet module']);exit;}\n"
if apply:
    runner_source = runner_source.replace("if (function_exists('opcache_invalidate'))", install_helper + "if (function_exists('opcache_invalidate'))", 1)
connection = ['curl', '--fail', '--silent', '--show-error', '--ssl-reqd', '--tlsv1.2', '--cacert', str(ROOT / 'ops/production/grass-ftps.pem'), '--pinnedpubkey', PIN, '--ciphers', 'DEFAULT:@SECLEVEL=1', '--connect-timeout', '20', '--max-time', '120', '--retry', '2', '--retry-delay', '2', '--user', os.environ['FTP_USERNAME'] + ':' + os.environ['FTP_PASSWORD']]

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
        (ROOT / 'catalog-repair-result.json').write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding='utf-8')
        print(json.dumps({key: value for key, value in result.items() if not isinstance(value, list)}, ensure_ascii=False))
        if result.get('ok') is not True:
            raise RuntimeError('Product import failed')
    finally:
        transfer(['--quote', 'DELE /public/' + name, '--output', os.devnull, 'ftp://grass.gohost.ru:21//'])
if apply:
    from verify_catalog_repairs import verify
    report = verify(result, all_sources)
    (ROOT / 'catalog-repair-verification.json').write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps(report, ensure_ascii=False))
