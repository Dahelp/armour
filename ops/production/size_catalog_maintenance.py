"""Audit catalog sizes through existing pinned FTPS access; always remove runner."""
import json
import base64
import hashlib
import hmac
import re
import os
from pathlib import Path
import secrets
import subprocess
import tempfile
import urllib.request
import urllib.error
from deploy_ftps import ROOT, PIN

def crypt_report(payload, decrypt=False):
    command=['openssl','enc','-aes-256-cbc','-pbkdf2','-iter','200000','-pass','env:FTP_PASSWORD']
    if decrypt:
        command.append('-d')
    operation=subprocess.run(command,input=payload,stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    if operation.returncode:
        raise RuntimeError('Protected report encryption failed')
    return operation.stdout

def report_mac(payload):
    key=hmac.new(os.environ['FTP_PASSWORD'].encode(),b'TechTires size report authentication',hashlib.sha256).digest()
    return hmac.new(key,payload,hashlib.sha256).digest()

token = secrets.token_hex(32)
name = '__size_catalog_' + secrets.token_hex(16) + '.php'
body = (ROOT/'bin/size_catalog_maintenance.php').read_text(encoding='utf-8').removeprefix('<?php').replace('declare(strict_types=1);', '', 1)
mode='true' if os.environ.get('APPLY')=='true' else 'false'
plan=[]
if mode=='true':
    protected=Path(os.environ['AUDIT_REPORT']).read_bytes()
    if len(protected)<33 or not hmac.compare_digest(protected[:32],report_mac(protected[32:])):
        raise RuntimeError('Protected audit report authentication failed')
    audit=json.loads(crypt_report(protected[32:],decrypt=True))
    attributes={int(a['product_id']):a['attribute_text'] for a in audit['attributes'] if a['attribute_name']=='Размер шины'}
    bound={int(b['product_id']) for b in audit['bindings']}
    plan=[{'id':int(p['id']),'article':p['article'],'category_id':int(p['category_id']),'size':attributes[int(p['id'])]} for p in audit['products'] if int(p['id']) in attributes and int(p['id']) not in bound and re.search('шина|шины',p['name'],re.I) and not re.search('Камера|Диск|лента',p['name'],re.I)]
    digest=hashlib.sha256(json.dumps(plan,ensure_ascii=False,separators=(',',':')).encode()).hexdigest()
    if digest!='4ea6da27ffc3ab65cff7c4ee295cf75e076ae75e7d418fbee706872048cfb17d':
        raise RuntimeError('Artifact differs from the reviewed repair plan')
plan64=base64.b64encode(json.dumps(plan,ensure_ascii=False).encode()).decode()
source = "<?php\nheader('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');\nif($_SERVER['REQUEST_METHOD']!=='POST'||!hash_equals('"+token+"',(string)($_SERVER['HTTP_X_RUNNER_TOKEN']??''))){http_response_code(403);exit;}\n$sizeApply="+mode+";$sizePlan=json_decode(base64_decode('"+plan64+"'),true,512,JSON_THROW_ON_ERROR);\ntry{\n"+body+"\n}catch(Throwable $e){http_response_code(500);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}"
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
        try:
            response=urllib.request.urlopen(request,timeout=120)
        except urllib.error.HTTPError as error:
            response=error
        with response:
            result=json.load(response)
        cipher=crypt_report(json.dumps(result,ensure_ascii=False).encode())
        (ROOT/'size-catalog-report.enc').write_bytes(report_mac(cipher)+cipher)
        summary={'ok':result.get('ok'),'sizes':len(result.get('sizes',[])),'empty_sizes':sum(int(s['visible_products'])==0 for s in result.get('sizes',[])),'products':len(result.get('products',[])),'applied':len(result.get('applied',[])),'new_bindings':sum('alias' in item for item in result.get('applied',[])),'sitemap_urls':result.get('sitemap_urls')}
        if mode=='true' and result.get('ok'):
            normalize=lambda s: re.sub(r'\s+','',s.lower().replace('*','x').replace('х','x').replace('×','x'))
            values={int(s['id']):s for s in result['sizes']}
            bindings={}
            for binding in result['bindings']:
                bindings.setdefault(int(binding['product_id']),[]).append(values[int(binding['attr_id'])])
            summary['verified_bindings']=sum(any(normalize(v['value'])==normalize(item['size']) for v in bindings.get(item['id'],[])) for item in plan)
        (ROOT/'size-catalog-summary.json').write_text(json.dumps(summary),encoding='utf-8')
        print(json.dumps({'ok':result.get('ok'),'sizes':len(result.get('sizes',[])),'products':len(result.get('products',[]))}))
        if result.get('ok') is not True:
            raise RuntimeError('Catalog size audit failed')
        if mode=='true' and summary.get('verified_bindings')!=len(plan):
            raise RuntimeError('Repaired bindings failed verification')
    finally:
        transfer(['--quote','DELE /public/'+name,'--output',os.devnull,'ftp://grass.gohost.ru:21//'])
