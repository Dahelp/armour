<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\services\ImageFileStorage;
use app\services\RemoteImageDownloader;

function assertImageStorage(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'techtires-image-' . bin2hex(random_bytes(6));
mkdir($temporaryDirectory, 0700, true);
$pngPath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'fake.php';
$textPath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'fake.jpg';
file_put_contents($pngPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
file_put_contents($textPath, '<?php echo "not an image";');

$_FILES['image'] = [
    'name' => 'shell.php',
    'type' => 'application/x-php',
    'tmp_name' => $pngPath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($pngPath),
];
ImageFileStorage::secureUploadField('image');
assertImageStorage($_FILES['image']['type'] === 'image/png', 'MIME type must come from file contents.');
assertImageStorage(str_ends_with($_FILES['image']['name'], '.png'), 'Stored extension must come from the detected MIME type.');
assertImageStorage(!str_contains($_FILES['image']['name'], 'shell'), 'Original upload name must not be reused.');

$_FILES['image'] = [
    'name' => 'photo.jpg', 'type' => 'image/jpeg', 'tmp_name' => $textPath,
    'error' => UPLOAD_ERR_OK, 'size' => filesize($textPath),
];
$rejected = false;
try {
    ImageFileStorage::secureUploadField('image');
} catch (RuntimeException) {
    $rejected = true;
}
assertImageStorage($rejected, 'A text or PHP file disguised as JPEG must be rejected.');

foreach (['../config.php', '..\\config.php', 'folder/photo.jpg', ''] as $name) {
    $rejected = false;
    try {
        ImageFileStorage::safeName($name);
    } catch (InvalidArgumentException) {
        $rejected = true;
    }
    assertImageStorage($rejected, 'Unsafe deletion path must be rejected.');
}

$deletePath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'delete-me.webp';
file_put_contents($deletePath, 'test');
assertImageStorage(ImageFileStorage::delete($temporaryDirectory, 'delete-me.webp'), 'Expected image was not deleted.');
assertImageStorage(!file_exists($deletePath), 'Deleted image still exists.');

$remoteRejected = false;
try {
    (new RemoteImageDownloader())->download('file:///etc/passwd', $temporaryDirectory);
} catch (InvalidArgumentException) {
    $remoteRejected = true;
}
assertImageStorage($remoteRejected, 'Remote images must reject non-HTTP sources.');

unlink($pngPath);
unlink($textPath);
rmdir($temporaryDirectory);
unset($_FILES['image']);

$root = dirname(__DIR__);
$uploadModels = [
    'Brand.php', 'Category.php', 'ContentsPages.php', 'FiltrsAttr.php', 'Product.php',
    'Review.php', 'PlaginsComplete.php', 'PlaginsTechnics.php',
    'PlaginsTechnicsManufacturer.php', 'PlaginsTechnicsType.php',
];
foreach ($uploadModels as $model) {
    $source = file_get_contents($root . '/app/models/admin/' . $model);
    assertImageStorage(str_contains($source, 'ImageFileStorage::secureUploadField($name)'), "$model bypasses image content validation.");
    assertImageStorage(!str_contains($source, 'md5(time())'), "$model still uses collision-prone image names.");
}

foreach (['Product.php', 'PlaginsTechnics.php'] as $model) {
    $source = file_get_contents($root . '/app/models/admin/' . $model);
    assertImageStorage(str_contains($source, 'new RemoteImageDownloader()'), "$model bypasses secure remote image downloading.");
    assertImageStorage(!str_contains($source, 'CURLOPT_SSL_VERIFYPEER, false'), "$model disables TLS verification.");
    assertImageStorage(!preg_match('/@copy\(\$img/', $source), "$model copies remote images without validation.");
}

foreach (glob($root . '/app/controllers/admin/*Controller.php') ?: [] as $controller) {
    $source = file_get_contents($controller);
    assertImageStorage(!preg_match('/@?unlink\(WWW[^;]*\$src/', $source), basename($controller) . ' performs an unsafe image deletion.');
}

echo "Image file storage tests passed.\n";
