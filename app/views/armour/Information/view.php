<div class="storefront-breadcrumb">
    <div class="col-full">
        <nav class="woocommerce-breadcrumb" aria-label="Хлебные крошки">
            <a href="<?= PATH ?>">Главная</a>
            <span class="breadcrumb-separator"> / </span>
            <?= h($page['title']) ?>
        </nav>
    </div>
</div>

<main class="information-page">
    <div class="col-full">
        <article class="information-page__card">
            <h1><?= h($page['title']) ?></h1>
            <?php require __DIR__ . '/_' . $key . '.php'; ?>
        </article>
    </div>
</main>
