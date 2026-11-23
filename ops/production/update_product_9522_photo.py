"""Publish the requested 9522 photo and verify every affected public product."""
import json
import os
from pathlib import Path
import secrets
import subprocess
import tempfile
import urllib.request
from deploy_ftps import ROOT, PIN

name = '__photo9522_' + secrets.token_hex(16) + '.php'
token = secrets.token_hex(32)
body = (ROOT / 'bin/update_product_9522_photo.php').read_text(encoding='utf-8')
source = "<?php\nheader('Content-Type: application/json');\nheader('Cache-Control: no-store');\nif ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals('" + token + "', (string)($_SERVER['HTTP_X_RUNNER_TOKEN'] ?? ''))) { http_response_code(403); exit; }\ntry {\n" + body.removeprefix('<?php') + "\n} catch (Throwable $e) { echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); }\n"
connection = ['curl', '--fail', '--silent', '--show-error', '--ssl-reqd', '--tlsv1.2',
              '--cacert', str(ROOT / 'ops/production/grass-ftps.pem'), '--pinnedpubkey', PIN,
              '--ciphers', 'DEFAULT:@SECLEVEL=1', '--connect-timeout', '20', '--max-time', '120',
              '--retry', '2', '--retry-delay', '2', '--user', os.environ['FTP_USERNAME'] + ':' + os.environ['FTP_PASSWORD']]

def transfer(options):
    if subprocess.run(connection + options, cwd=ROOT).returncode:
        raise RuntimeError('Pinned FTPS transfer failed')

with tempfile.TemporaryDirectory() as directory:
    runner = Path(directory) / name
    runner.write_text(source, encoding='utf-8')
    try:
        for folder in ('baseimg', 'mini'):
            transfer(['--ftp-create-dirs', '--upload-file', str(ROOT / 'resources/products/9522/17-5-25.jpg'),
                      'ftp://grass.gohost.ru:21//public/images/product/' + folder + '/9522-17-5-25-20261123.jpg'])
        transfer(['--upload-file', str(runner), 'ftp://grass.gohost.ru:21//public/' + name])
        request = urllib.request.Request('https://techtires.ru/' + name, data=b'', headers={'X-Runner-Token': token})
        with urllib.request.urlopen(request, timeout=90) as response:
            result = json.load(response)
        summary = {key: value for key, value in result.items() if key != 'products'}
        (ROOT / '9522-photo-result.json').write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding='utf-8')
        print(json.dumps(summary, ensure_ascii=False))
        if result.get('ok') is not True:
            raise RuntimeError('Photo update failed')
        for product in result['products']:
            with urllib.request.urlopen('https://techtires.ru/' + product['alias'], timeout=60) as response:
                html = response.read().decode('utf-8')
            if '/images/product/baseimg/' + result['image'] not in html:
                raise RuntimeError('Public photo verification failed for product ' + str(product['id']))
        with urllib.request.urlopen('https://techtires.ru/images/product/baseimg/' + result['image'], timeout=60) as response:
            if response.read() != (ROOT / 'resources/products/9522/17-5-25.jpg').read_bytes():
                raise RuntimeError('Public asset does not match uploaded photo')
        with urllib.request.urlopen('https://techtires.ru/images/product/mini/' + result['image'], timeout=60) as response:
            if response.read() != (ROOT / 'resources/products/9522/17-5-25.jpg').read_bytes():
                raise RuntimeError('Catalog photo does not match supplied photo')
        with urllib.request.urlopen('https://techtires.ru/search/?s=9522', timeout=60) as response:
            if result['image'] not in response.read().decode('utf-8'):
                raise RuntimeError('Search card still shows another photo')
        print('Verified product 9522, exact original bytes in both image folders and the search card')
    finally:
        transfer(['--quote', 'DELE /public/' + name, '--output', os.devnull, 'ftp://grass.gohost.ru:21//'])
