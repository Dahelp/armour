<?php
// PHP 5.6-compatible: runs in the original Armour hosting account.
require_once __DIR__.'/__owner_bridge_ticket.php';
header('Cache-Control: no-store, private, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Content-Type-Options: nosniff');
$configFile = __DIR__.'/.owner-bridge-config.php';
$config = is_file($configFile) ? require $configFile : array();
$secret = isset($config['secret']) ? $config['secret'] : '';
$ownerId = isset($config['owner_id']) ? (int)$config['owner_id'] : 0;
$legacyId = isset($config['legacy_id']) ? (int)$config['legacy_id'] : 0;
$path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);

function ownerBridgeReject($path) {
    if ($path === '/__owner_bridge.php') {
        http_response_code(404);
        echo 'Страница не найдена';
    } else {
        header('Location: https://techtires.ru'.$path, true, 301);
    }
    exit;
}
function ownerBridgeCookie($name, $value, $expires) {
    header('Set-Cookie: '.$name.'='.rawurlencode($value).'; Expires='.gmdate('D, d M Y H:i:s', $expires).' GMT; Max-Age='.max(0, $expires-time()).'; Path=/; Secure; HttpOnly; SameSite=Lax', false);
}
if (strlen($secret) < 64 || $ownerId < 1 || $legacyId < 1 || empty($config['legacy_role']) || (int)$config['legacy_role'] === 1) ownerBridgeReject($path);
if (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) === 'off') ownerBridgeReject($path);
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if ($path === '/__owner_bridge.php') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') ownerBridgeReject($path);
    $claims = \app\services\LegacyAdminTicket::verify(isset($_POST['ticket']) ? $_POST['ticket'] : '', $secret, 'legacy-admin-launch', $ownerId, time(), 45);
    if (!$claims) ownerBridgeReject($path);
    $stateDirectory = __DIR__.'/.owner-bridge-state';
    if (!is_dir($stateDirectory) && !mkdir($stateDirectory, 0700)) ownerBridgeReject($path);
    $handle = @fopen($stateDirectory.'/nonce_'.hash('sha256', $claims['nonce']), 'x');
    if (!$handle) ownerBridgeReject($path);
    fwrite($handle, (string)$claims['exp']);
    fclose($handle);
    foreach (glob($stateDirectory.'/nonce_*') as $oldNonce) {
        if (preg_match('/^nonce_[a-f0-9]{64}$/D', basename($oldNonce)) && filemtime($oldNonce) < time()-3600) @unlink($oldNonce);
    }
    session_regenerate_id(true);
    $_SESSION['auth'] = array('admin'=>htmlspecialchars($config['legacy_name'], ENT_QUOTES, 'UTF-8'), 'user_id'=>$legacyId, 'role'=>$config['legacy_role']);
    $_SESSION['owner_bridge_expires'] = time()+3600;
    $cookieClaims = array('purpose'=>'legacy-admin-session', 'uid'=>$ownerId, 'exp'=>$_SESSION['owner_bridge_expires'], 'nonce'=>$claims['nonce'], 'sid'=>hash('sha256', session_id()));
    ownerBridgeCookie('armour_owner_access', \app\services\LegacyAdminTicket::sign($cookieClaims, $secret), $cookieClaims['exp']);
    header('Location: https://armour-shina.ru/admin/?view=pages', true, 303);
    exit;
}
$claims = \app\services\LegacyAdminTicket::verify(isset($_COOKIE['armour_owner_access']) ? $_COOKIE['armour_owner_access'] : '', $secret, 'legacy-admin-session', $ownerId, time(), 3600);
if (!$claims || !isset($claims['sid']) || !hash_equals(hash('sha256', session_id()), $claims['sid']) || empty($_SESSION['auth']['admin']) || (int)$_SESSION['auth']['user_id'] !== $legacyId || (int)$_SESSION['auth']['role'] !== (int)$config['legacy_role'] || empty($_SESSION['owner_bridge_expires']) || $_SESSION['owner_bridge_expires'] <= time()) ownerBridgeReject($path);

$relative = ltrim(rawurldecode($path), '/');
if (strpos($relative, "\0") !== false || strpos($relative, '\\') !== false || preg_match('#(?:^|/)\.#', $relative)) ownerBridgeReject($path);
if (!preg_match('#^(?:admin(?:/|$)|userfiles/|views/|favicon\.ico$|Logo_round\.png$)#D', $relative)) ownerBridgeReject($path);
$target = __DIR__.'/'.$relative;
if (is_dir($target)) $target = rtrim($target, '/').'/index.php';
$target = realpath($target);
$root = realpath(__DIR__).DIRECTORY_SEPARATOR;
if ($target === false || strpos($target, $root) !== 0 || !is_file($target)) { http_response_code(404); exit('Страница не найдена'); }
$extension = strtolower(pathinfo($target, PATHINFO_EXTENSION));
if ($extension === 'php') {
    if (strpos($relative, 'admin/') !== 0 && $relative !== 'admin') ownerBridgeReject($path);
    $_SERVER['SCRIPT_NAME'] = '/'.substr($target, strlen($root));
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    $_SERVER['SCRIPT_FILENAME'] = $target;
    chdir(dirname($target));
    require $target;
    exit;
}
$types = array('css'=>'text/css', 'js'=>'application/javascript', 'png'=>'image/png', 'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'gif'=>'image/gif', 'svg'=>'image/svg+xml', 'ico'=>'image/x-icon', 'woff'=>'font/woff', 'woff2'=>'font/woff2', 'ttf'=>'font/ttf', 'eot'=>'application/vnd.ms-fontobject', 'html'=>'text/html');
if (!isset($types[$extension])) ownerBridgeReject($path);
header('Content-Type: '.$types[$extension]);
session_write_close();
readfile($target);
