<?php
// Protected one-off runner body, used by the oil-filter URL migration workflow.
require dirname(__DIR__) . '/config/init.php';
\ishop\Db::instance();
$plan = json_decode(file_get_contents(ROOT . '/resources/repairs/oil-filter-urls.json'), true, 512, JSON_THROW_ON_ERROR);
\R::begin();
try {
    foreach ($plan as $row) {
        $product = \R::getRow('SELECT p.*, c.alias AS category_alias FROM product p JOIN category c ON c.id=p.category_id WHERE p.id=? FOR UPDATE', [$row['id']]);
        if (!$product || $product['name'] !== $row['name'] || $product['category_alias'] !== 'filtry-maslyanye' || !in_array($product['alias'], [$row['old'], $row['new']], true)) {
            throw new RuntimeException('Unexpected product: ' . $row['id']);
        }
        if (\R::getCell('SELECT COUNT(*) FROM product WHERE alias=? AND id<>?', [$row['new'], $row['id']]) || \R::getCell("SELECT COUNT(*) FROM url_alias WHERE sef=? AND (view<>'Product' OR urlid<>?)", [$row['new'], $row['id']])) {
            throw new RuntimeException('URL collision: ' . $row['new']);
        }
        \R::exec('UPDATE product SET alias=? WHERE id=?', [$row['new'], $row['id']]);
        \R::exec("DELETE FROM url_alias WHERE sef=? AND view='Product' AND urlid=?", [$row['old'], $row['id']]);
        (new \app\services\UrlAliasRepository())->save($row['new'], 'Product', (int)$row['id']);
    }
    \R::commit();
    echo json_encode(['ok'=>true, 'products'=>count($plan)]);
} catch (Throwable $error) {
    \R::rollback();
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>$error->getMessage()]);
}
