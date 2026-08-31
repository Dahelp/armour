<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function sqlDumpAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$fixture = tempnam(sys_get_temp_dir(), 'sql-reader-');
if ($fixture === false) {
    throw new RuntimeException('Unable to create fixture.');
}
file_put_contents($fixture, "INSERT INTO `url_alias` (`id`, `sef`, `view`, `urlid`) VALUES\n"
    . "(1, 'old-product.html', 'product', 7),\n"
    . "(2, 'quote\\'s-path', 'category', NULL);\n"
    . "INSERT INTO `user` (`id`, `email`) VALUES (1, 'private@example.com');\n");

$reader = new \app\services\SqlDumpTableReader();
$rows = $reader->readTable($fixture, 'url_alias');
@unlink($fixture);

sqlDumpAssert(count($rows) === 2, 'Selected rows were not parsed.');
sqlDumpAssert($rows[0]['sef'] === 'old-product.html' && $rows[0]['urlid'] === 7, 'String or integer value is incorrect.');
sqlDumpAssert($rows[1]['sef'] === "quote's-path" && $rows[1]['urlid'] === null, 'Escaped or NULL value is incorrect.');
sqlDumpAssert($reader->readTable(__FILE__, 'missing_table') === [], 'Missing table did not produce an empty result.');

echo "SQL dump table reader checks passed.\n";
