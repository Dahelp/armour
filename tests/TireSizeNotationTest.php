<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
use app\services\TireSizeNotation as Size;

function sizeAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

foreach (['18*7-8', '18x7-8', '18X7-8', '18х7-8', '18Х7-8', '18×7-8', '18 x 7-8', "18\u{00A0}×\u{00A0}7-8"] as $input) {
    sizeAssert(Size::display($input) === '18x7-8', 'Display differs: ' . $input);
}
foreach (['6.50-10', '200/50-10', '15x4 1/2-8', '12.00R20', 'Техника Х', 'Наличие *', '<ul><li>10PR</li></ul>'] as $input) {
    sizeAssert(Size::display($input) === $input, 'Meaningful notation/content changed: ' . $input);
}

// Execute real SQL over differently written stored names/attributes, not only PHP keys.
$db = new PDO('sqlite::memory:');
$db->sqliteCreateFunction('regexp', static fn($pattern, $value) => preg_match('~' . $pattern . '~iu', (string)$value) === 1, 2);
$db->exec('CREATE TABLE sizes (id INTEGER PRIMARY KEY, value TEXT)');
$insert = $db->prepare('INSERT INTO sizes VALUES (?, ?)');
$stored = ['18*7-8', '18 x 7 - 8', '18Х7-8', '18×7-8', "18\u{202F}х\u{202F}7-8", '18x8-8', '6,50-10', '6.50-10', '15*4 1/2-8', '15 x 4 1/2 - 8', '200/50-10', '12.00R20', 'модель, шина'];
foreach ($stored as $id => $value) $insert->execute([$id, $value]);
function fixturePredicate(string $query): string
{
    // SQLite has no MySQL CONVERT USING/collations; keep the same REGEXP and bindings.
    return str_replace(
        ['CONVERT(value USING utf8mb4) COLLATE utf8mb4_unicode_ci', 'CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci'],
        ['value', '?'],
        Size::searchExpression('value', $query)
    );
}
sizeAssert(substr_count(Size::searchExpression('value', '18x7-8'), 'COLLATE utf8mb4_unicode_ci') === 2, 'Both REGEXP operands must use the same collation.');
foreach (['18*7-8', '18 x 7 - 8', '18X7-8', '18 х 7-8', '18×7-8'] as $query) {
    $sql = 'SELECT id FROM sizes WHERE ' . fixturePredicate($query) . ' ORDER BY id';
    $stmt = $db->prepare($sql);
    $stmt->execute([Size::searchPattern($query)]);
    sizeAssert($stmt->fetchAll(PDO::FETCH_COLUMN) === [0, 1, 2, 3, 4], 'SQL size matching differs: ' . $query);
}
foreach (['6.50-10' => [6, 7], '15x4 1/2-8' => [8, 9], '200/50-10' => [10], '12.00R20' => [11], 'модель, шина' => [12], '99x99-99' => []] as $query => $expected) {
    $stmt = $db->prepare('SELECT id FROM sizes WHERE ' . fixturePredicate($query) . ' ORDER BY id');
    $stmt->execute([Size::searchPattern($query)]);
    sizeAssert($stmt->fetchAll(PDO::FETCH_COLUMN) === $expected, 'Unexpected result for: ' . $query);
}

require dirname(__DIR__) . '/vendor/ishop/core/libs/functions.php';
$product = ['id' => 1, 'name' => 'Шина 18*7-8 <пример>', 'alias' => 'shina-18-7-8', 'quantity' => 1];
$curr = ['value' => 1];
ob_start();
require dirname(__DIR__) . '/app/widgets/product/product_tpl.php';
$html = ob_get_clean();
sizeAssert(str_contains($html, 'Шина 18x7-8 &lt;пример&gt;'), 'Rendered name must be normalised and escaped.');
sizeAssert(str_contains($html, 'href="/shina-18-7-8"'), 'Existing product URL changed.');
sizeAssert($product['name'] === 'Шина 18*7-8 <пример>', 'Rendering must not mutate source data.');

echo "Tire size notation checks passed.\n";
