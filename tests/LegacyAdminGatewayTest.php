<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/services/LegacyAdminTicket.php';
use app\services\LegacyAdminTicket;

$root = dirname(__DIR__);
$directory = $root.'/tmp/reports/legacy-gateway-test-'.bin2hex(random_bytes(6));
mkdir($directory.'/admin',0700,true);
mkdir($directory.'/sessions',0700);
copy($root.'/ops/legacy-admin-bridge/gateway.php',$directory.'/__owner_bridge.php');
copy($root.'/app/services/LegacyAdminTicket.php',$directory.'/__owner_bridge_ticket.php');
$key = str_repeat('a',64);
file_put_contents($directory.'/.owner-bridge-config.php','<?php return '.var_export(['secret'=>$key,'owner_id'=>42,'legacy_id'=>123,'legacy_role'=>2,'legacy_name'=>'Owner'],true).';');
file_put_contents($directory.'/router.php','<?php $_SERVER["HTTPS"]="on"; require __DIR__."/__owner_bridge.php";');
file_put_contents($directory.'/admin/index.php','<?php echo "owner=".(int)$_SESSION["auth"]["user_id"];');
file_put_contents($directory.'/admin/style.css','body { color: black; }');
$socket = stream_socket_server('tcp://127.0.0.1:0',$errno,$message);
$address = stream_socket_get_name($socket,false);
fclose($socket);
$pipes = [];
$process = proc_open([PHP_BINARY,'-d','session.save_path='.$directory.'/sessions','-S',$address,'-t',$directory,$directory.'/router.php'],[0=>['pipe','r'],1=>['file',$directory.'/server.log','a'],2=>['file',$directory.'/server.log','a']],$pipes);
if (!is_resource($process)) throw new RuntimeException('Test server failed.');
fclose($pipes[0]);
$cookies = [];
$request = static function(string $path, ?string $ticket = null, ?array $overrideCookies = null) use ($address,&$cookies): array {
    $headers = [];
    $handle = curl_init('http://'.$address.$path);
    curl_setopt_array($handle,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>5,CURLOPT_HEADERFUNCTION=>static function($handle,string $line) use (&$headers,&$cookies): int {
        $headers[] = trim($line);
        if (preg_match('/^Set-Cookie: ([^=]+)=([^;]*)/i',$line,$match)) $cookies[$match[1]]=rawurldecode($match[2]);
        return strlen($line);
    }]);
    $sentCookies = $overrideCookies ?? $cookies;
    curl_setopt($handle,CURLOPT_COOKIE,implode('; ',array_map(static fn($name,$value)=>$name.'='.rawurlencode($value),array_keys($sentCookies),$sentCookies)));
    if ($ticket !== null) curl_setopt_array($handle,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['ticket'=>$ticket])]);
    $body = curl_exec($handle);
    return ['status'=>curl_getinfo($handle,CURLINFO_RESPONSE_CODE),'body'=>$body,'headers'=>$headers];
};
try {
    for ($i=0;$i<40;$i++) {
        $ready = @stream_socket_client('tcp://'.$address,$errno,$message,0.1);
        if ($ready) { fclose($ready); break; }
        usleep(50000);
    }
    if ($request('/admin/')['status'] !== 301) throw new RuntimeException('Anonymous access allowed.');
    $claims = ['purpose'=>'legacy-admin-launch','uid'=>42,'exp'=>time()+45,'nonce'=>bin2hex(random_bytes(32))];
    $ticket = LegacyAdminTicket::sign($claims,$key);
    $response = $request('/__owner_bridge.php',$ticket);
    if ($response['status'] !== 303) throw new RuntimeException('Owner handoff failed.');
    if ($request('/admin/')['body'] !== 'owner=123') throw new RuntimeException('Legacy owner session missing.');
    if ($request('/admin/style.css')['body'] !== 'body { color: black; }') throw new RuntimeException('Private assets failed.');
    $savedCookies = $cookies;
    if ($request('/admin/',null,['armour_owner_access'=>$cookies['armour_owner_access']])['status'] !== 301) throw new RuntimeException('Gate cookie accepted without bound session.');
    $cookies = $savedCookies;
    if ($request('/__owner_bridge.php',$ticket)['status'] !== 404) throw new RuntimeException('Replay accepted.');
    if ($request('/admin/../.owner-bridge-config.php')['status'] !== 301) throw new RuntimeException('Private file path accepted.');
    echo "Legacy gateway owner session, private assets, replay and session-binding checks passed.\n";
} finally {
    proc_terminate($process);
    proc_close($process);
    foreach (glob($directory.'/.owner-bridge-state/nonce_*') ?: [] as $file) unlink($file);
    foreach (glob($directory.'/sessions/sess_*') ?: [] as $file) unlink($file);
    foreach (['admin/index.php','admin/style.css','__owner_bridge.php','__owner_bridge_ticket.php','.owner-bridge-config.php','router.php','server.log'] as $file) unlink($directory.'/'.$file);
    @rmdir($directory.'/.owner-bridge-state');
    rmdir($directory.'/sessions');
    rmdir($directory.'/admin');
    rmdir($directory);
}
