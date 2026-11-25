"""Audit catalog sizes through existing pinned FTPS access; always remove runner."""
import json
import os
from pathlib import Path
import secrets
import subprocess
import tempfile
import urllib.request
from deploy_ftps import ROOT, PIN

token = secrets.token_hex(32)
name = '__size_catalog_' + secrets.token_hex(16) + '.php'
body = (ROOT/'bin/size_catalog_maintenance.php').read_text(encoding='utf-8').removeprefix('<?php').replace('declare(strict_types=1);', '', 1)
source = "<?php\nheader('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');\nif($_SERVER['REQUEST_METHOD']!=='POST'||!hash_equals('"+token+"',(string)($_SERVER['HTTP_X_RUNNER_TOKEN']??''))){http_response_code(403);exit;}\ntry{\n"+body+"\n}catch(Throwable $e){http_response_code(500);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}"
connection=['curl','--fail','--silent','--show-error','--ssl-reqd','--tlsv1.2','--cacert',str(ROOT/'ops/production/grass-ftps.pem'),'--pinnedpubkey',PIN,'--ciphers','DEFAULT:@SECLEVEL=1','--connect-timeout','20','--max-time','120','--user',os.environ['FTP_USERNAME']+':'+os.environ['FTP_PASSWORD']]
def transfer(options):
    if subprocess.run(connection+options,cwd=ROOT).returncode:
        raise RuntimeError('Pinned FTPS transfer failed')
with tempfile.TemporaryDirectory() as directory:
    runner=Path(directory)/name
    runner.write_text(source,encoding='utf-8')
    subprocess.run(['php','-l',str(runner)],check=True)
    try:
        transfer(['--upload-file',str(runner),'ftp://grass.gohost.ru:21//public/'+name])
        request=urllib.request.Request('https://techtires.ru/'+name,data=b'',headers={'X-Runner-Token':token})
        with urllib.request.urlopen(request,timeout=120) as response:
            result=json.load(response)
        (ROOT/'size-catalog-report.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf-8')
        print(json.dumps({'ok':result.get('ok'),'sizes':len(result.get('sizes',[])),'products':len(result.get('products',[]))}))
        if result.get('ok') is not True:
            raise RuntimeError('Catalog size audit failed')
    finally:
        transfer(['--quote','DELE /public/'+name,'--output',os.devnull,'ftp://grass.gohost.ru:21//'])
