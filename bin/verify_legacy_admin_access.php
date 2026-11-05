<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/services/LegacyAdminTicket.php';
use app\services\LegacyAdminTicket;

$config = require $argv[1];
$cookieJar = dirname($argv[1]).'/legacy-test-cookies';
function legacyRequest(string $path, ?string $ticket = null, ?string $cookieJar = null, ?string $cookie = null): array {
    $headers = [];
    $handle = curl_init('https://armour-shina.ru'.$path);
    curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>30, CURLOPT_HEADERFUNCTION=>static function($handle,string $line) use (&$headers): int { if (str_contains($line, ':')) { [$key,$value]=explode(':',$line,2); $headers[strtolower(trim($key))]=trim($value); } return strlen($line); }]);
    if ($ticket !== null) curl_setopt_array($handle, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['ticket'=>$ticket])]);
    if ($cookieJar !== null) curl_setopt_array($handle, [CURLOPT_COOKIEJAR=>$cookieJar,CURLOPT_COOKIEFILE=>$cookieJar]);
    if ($cookie !== null) curl_setopt($handle,CURLOPT_COOKIE,$cookie);
    $body = curl_exec($handle);
    if ($body === false) throw new RuntimeException('Legacy request failed.');
    $status = curl_getinfo($handle,CURLINFO_RESPONSE_CODE);
    unset($handle); // Flush the cookie jar without exporting its contents.
    return ['status'=>$status,'headers'=>$headers,'body'=>$body];
}
foreach (['/','/admin/','/crossing-33628.html'] as $path) {
    $response = legacyRequest($path);
    if ($response['status'] !== 301 || ($response['headers']['location'] ?? '') !== 'https://techtires.ru'.$path) throw new RuntimeException('Public redirect changed: '.$path);
}
$response = legacyRequest('/admin/', null, null, 'armour_owner_access=forged');
if ($response['status'] !== 301) throw new RuntimeException('Forged cookie opened the legacy admin.');
$claims = ['purpose'=>'legacy-admin-launch','uid'=>(int)$config['owner_id'],'exp'=>time()+45,'nonce'=>bin2hex(random_bytes(32))];
$wrong = $claims;
$wrong['uid']++;
if (legacyRequest('/__owner_bridge.php',LegacyAdminTicket::sign($wrong,$config['secret']))['status'] !== 404) throw new RuntimeException('Other account ticket accepted.');
$expired = $claims;
$expired['exp'] = time()-1;
if (legacyRequest('/__owner_bridge.php',LegacyAdminTicket::sign($expired,$config['secret']))['status'] !== 404) throw new RuntimeException('Expired ticket accepted.');
$ticket = LegacyAdminTicket::sign($claims,$config['secret']);
$response = legacyRequest('/__owner_bridge.php',$ticket,$cookieJar);
if ($response['status'] !== 303) throw new RuntimeException('Owner launch failed: HTTP '.$response['status']);
$response = legacyRequest('/admin/?view=pages',null,$cookieJar);
if ($response['status'] !== 200 || !str_contains($response['body'],'Панель администратирования')) throw new RuntimeException('Original admin did not open: HTTP '.$response['status']);
if (legacyRequest('/__owner_bridge.php',$ticket)['status'] !== 404) throw new RuntimeException('Single-use ticket was replayed.');
$response = legacyRequest('/admin/templates/style.css',null,$cookieJar);
if ($response['status'] !== 200 || !str_contains($response['headers']['content-type'] ?? '', 'text/css')) throw new RuntimeException('Private admin assets failed.');
@unlink($cookieJar);
echo "Owner admin login, private assets, replay rejection, invalid-ticket rejection and public redirects verified.\n";
