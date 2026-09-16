<?=\app\models\Breadcrumbs::render([['label' => $page['title']]])?>

<main class="information-page">
    <div class="col-full">
        <article class="information-page__card">
            <h1><?= h($page['title']) ?></h1>
            <?php require APP . '/views/' . TEMPLATE . '/Information/_' . $key . '.php'; ?>
        </article>
    </div>
</main>
