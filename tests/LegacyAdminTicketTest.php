<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/services/LegacyAdminTicket.php';
use app\services\LegacyAdminTicket;

$key = str_repeat('a',64);
$claims = ['purpose'=>'legacy-admin-launch','uid'=>42,'exp'=>1045,'nonce'=>str_repeat('b',64)];
$ticket = LegacyAdminTicket::sign($claims,$key);
if (LegacyAdminTicket::verify($ticket,$key,'legacy-admin-launch',42,1000,45) !== $claims) throw new RuntimeException('Owner ticket rejected.');
foreach ([
    [$ticket.'x',$key,'legacy-admin-launch',42,1000,45],
    [$ticket,str_repeat('c',64),'legacy-admin-launch',42,1000,45],
    [$ticket,$key,'legacy-admin-session',42,1000,3600],
    [$ticket,$key,'legacy-admin-launch',7,1000,45],
    [$ticket,$key,'legacy-admin-launch',42,1045,45],
    [$ticket,$key,'legacy-admin-launch',42,999,45],
    ['invalid',$key,'legacy-admin-launch',42,1000,45],
] as $arguments) {
    if (LegacyAdminTicket::verify(...$arguments) !== false) throw new RuntimeException('Invalid ticket accepted.');
}
$root = dirname(__DIR__);
$controller = file_get_contents($root.'/app/controllers/admin/LegacyController.php');
$layout = file_get_contents($root.'/app/views/armour/layouts/admin.php');
if (!str_contains($controller, 'SELECT id, role FROM user WHERE id = ?') || !str_contains($layout, 'LegacyAdminAccess::allowed')) throw new RuntimeException('Owner access guard missing.');
echo "Legacy owner ticket tests passed.\n";
