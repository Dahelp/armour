"""Execute the reviewed oil URL migration, removing the authenticated runner afterwards."""
import os
from pathlib import Path
import secrets
import subprocess
import tempfile
import urllib.request
from deploy_ftps import ROOT, PIN

name = '__oil_urls_' + secrets.token_hex(16) + '.php'
token = secrets.token_hex(32)
body = (ROOT / 'bin/migrate_oil_filter_urls.php').read_text(encoding='utf-8-sig')
source = "<?php\nheader('Content-Type: application/json');\nheader('Cache-Control: no-store');\nif (!hash_equals('" + token + "', (string)($_SERVER['HTTP_X_RUNNER_TOKEN'] ?? ''))) { http_response_code(403); exit; }\n" + body.removeprefix('<?php')
connection = ['curl', '--fail', '--silent', '--show-error', '--ssl-reqd', '--tlsv1.2',
              '--cacert', str(ROOT / 'ops/production/grass-ftps.pem'), '--pinnedpubkey', PIN,
              '--ciphers', 'DEFAULT:@SECLEVEL=1', '--connect-timeout', '20', '--max-time', '120',
              '--user', os.environ['FTP_USERNAME'] + ':' + os.environ['FTP_PASSWORD']]
def transfer(options):
    if subprocess.run(connection + options, cwd=ROOT).returncode:
        raise RuntimeError('Pinned FTPS transfer failed')
with tempfile.TemporaryDirectory() as directory:
    runner = Path(directory) / name
    runner.write_text(source, encoding='utf-8')
    try:
        transfer(['--upload-file', str(runner), 'ftp://grass.gohost.ru:21//public/' + name])
        request = urllib.request.Request('https://techtires.ru/' + name, data=b'', headers={'X-Runner-Token': token})
        with urllib.request.urlopen(request, timeout=90) as response:
            import json
            result = json.load(response)
            print(json.dumps(result))
            if result.get('ok') is not True:
                raise RuntimeError('Migration failed')
    finally:
        transfer(['--quote', 'DELE /public/' + name, 'ftp://grass.gohost.ru:21//'])
