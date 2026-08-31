<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\services\PluginStoragePolicy;
use app\services\RemoteXmlDownloader;

function assertImportSecurity(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

assertImportSecurity(PluginStoragePolicy::table('complete') === 'plagins_complete', 'Complete table mapping is invalid.');
assertImportSecurity(PluginStoragePolicy::table('technics') === 'technics', 'Technics table mapping is invalid.');
assertImportSecurity(PluginStoragePolicy::table('complete', '_gallery') === 'plagins_complete_gallery', 'Gallery table mapping is invalid.');

foreach ([['../../product', ''], ['complete; DROP TABLE product', ''], ['cross', '_gallery'], ['technics', '_gallery']] as [$plugin, $suffix]) {
    $rejected = false;
    try {
        PluginStoragePolicy::table($plugin, $suffix);
    } catch (InvalidArgumentException) {
        $rejected = true;
    }
    assertImportSecurity($rejected, 'Unsafe plugin identifier must be rejected.');
}

foreach (['../config.php', '..\\config.php', 'folder/image.webp', ''] as $name) {
    $rejected = false;
    try {
        PluginStoragePolicy::imageName($name);
    } catch (InvalidArgumentException) {
        $rejected = true;
    }
    assertImportSecurity($rejected, 'Unsafe image path must be rejected.');
}
assertImportSecurity(PluginStoragePolicy::imageName('product-42.webp') === 'product-42.webp', 'Safe image name was rejected.');

foreach (['file:///etc/passwd', 'ftp://example.org/products.xml', 'http://127.0.0.1/private.xml'] as $url) {
    $rejected = false;
    try {
        (new RemoteXmlDownloader())->download($url, sys_get_temp_dir());
    } catch (InvalidArgumentException) {
        $rejected = true;
    }
    assertImportSecurity($rejected, 'Unsafe import URL must be rejected before download.');
}

$root = dirname(__DIR__);
$import = file_get_contents($root . '/app/controllers/admin/ImportController.php');
$plugins = file_get_contents($root . '/app/controllers/admin/PlaginsController.php');

assertImportSecurity(!str_contains($import, 'CURLOPT_SSL_VERIFYPEER, false'), 'Product import must verify TLS.');
assertImportSecurity(str_contains($import, 'new UrlAliasRepository()'), 'Product import must use the shared URL alias repository.');
assertImportSecurity(!str_contains($import, '$sql_part'), 'Product attributes must not be concatenated into SQL.');
assertImportSecurity(!str_contains($plugins, 'UPDATE plagins_cross SET cross_id=\''), 'Cross import updates must use bindings.');
assertImportSecurity(!str_contains($plugins, 'findLast(\'technics_manufacturer\')'), 'Technics import must use actual inserted ids.');
assertImportSecurity(str_contains($plugins, 'PluginStoragePolicy::table'), 'Dynamic plugin tables must be allow-listed.');
assertImportSecurity(str_contains($plugins, 'WHERE complete_id = ? AND img = ?'), 'Complete gallery deletion must use its real owner column.');

echo "Import security tests passed.\n";
