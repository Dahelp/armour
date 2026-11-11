<?php
$root = dirname(__DIR__);
$plan = json_decode(file_get_contents($root . '/resources/repairs/oil-filter-urls.json'), true, 512, JSON_THROW_ON_ERROR);
if (count($plan) !== 25 || count(array_unique(array_column($plan, 'new'))) !== 25) throw new RuntimeException('Unexpected oil URL map');
foreach ($plan as $row) {
    if (!preg_match('/^filter-\d+$/', $row['old']) || !preg_match('/^ek-\d+-maslyanyj-filtr$/', $row['new'])) throw new RuntimeException('Invalid URL');
}
define('ROOT', $root);
define('PATH', 'https://techtires.ru');
foreach ($plan as $row) {
    $urli = $row['old'];
    $_SERVER['REQUEST_URI'] = '/' . $urli;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    require $root . '/config/oil_filter_redirects.php';
}
echo "Oil filter URL map tests passed\n";