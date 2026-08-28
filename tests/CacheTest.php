<?php

declare(strict_types=1);

$cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'techtires-cache-test-' . bin2hex(random_bytes(6));
define('CACHE', $cacheDirectory);

require dirname(__DIR__) . '/vendor/autoload.php';

function cacheAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cache = \ishop\Cache::instance();
cacheAssert($cache->set('catalog', ['ids' => [1, 2, 3]], 60), 'Cache write failed.');
cacheAssert($cache->get('catalog') === ['ids' => [1, 2, 3]], 'Cache round trip failed.');
cacheAssert($cache->set('disabled', 'value', 0) === false, 'Zero TTL must not create a cache entry.');

$files = glob($cacheDirectory . DIRECTORY_SEPARATOR . '*.cache') ?: [];
cacheAssert(count($files) === 1, 'Unexpected cache file count.');
file_put_contents($files[0], 'corrupt-payload', LOCK_EX);
cacheAssert($cache->get('catalog') === false, 'Corrupt cache data must be rejected.');
cacheAssert(!is_file($files[0]), 'Corrupt cache data must be removed.');

cacheAssert($cache->set('delete-me', 'value', 60), 'Second cache write failed.');
cacheAssert($cache->delete('delete-me'), 'Cache deletion failed.');
cacheAssert($cache->get('delete-me') === false, 'Deleted cache entry is still readable.');

cacheAssert($cache->set('one', 1, 60), 'Clear fixture one failed.');
cacheAssert($cache->set('two', 2, 60), 'Clear fixture two failed.');
cacheAssert($cache->clear(), 'Cache clear failed.');
cacheAssert((glob($cacheDirectory . DIRECTORY_SEPARATOR . '*.cache') ?: []) === [], 'Cache clear left files behind.');

$filterReflection = new ReflectionClass(\app\widgets\filter\Filter::class);
$filter = $filterReflection->newInstanceWithoutConstructor();
$cacheKeyMethod = $filterReflection->getMethod('cacheKey');
$firstScope = $cacheKeyMethod->invoke($filter, 'filter_attrs', '1,2,3');
$secondScope = $cacheKeyMethod->invoke($filter, 'filter_attrs', '4,5');
cacheAssert($firstScope !== $secondScope, 'Different category scopes must not share filter cache keys.');

@rmdir($cacheDirectory);
echo "File cache checks passed.\n";
