<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>Страница не найдена — TechTires</title>
    <style>
        body { margin: 0; background: #f5f7fa; color: #18212b; font: 16px/1.5 Arial, sans-serif; }
        main { box-sizing: border-box; max-width: 720px; min-height: 80vh; margin: 0 auto; padding: 12vh 24px 48px; text-align: center; }
        strong { display: block; color: #d9272e; font-size: clamp(72px, 18vw, 144px); line-height: 1; }
        h1 { margin: 24px 0 12px; font-size: clamp(28px, 5vw, 42px); }
        p { margin: 0 auto 28px; max-width: 560px; color: #586575; }
        a { display: inline-block; border-radius: 6px; background: #d9272e; padding: 12px 22px; color: #fff; text-decoration: none; }
        a:focus, a:hover { background: #b91f25; }
    </style>
</head>
<body>
<main>
    <strong>404</strong>
    <h1>Страница не найдена</h1>
    <p>Возможно, адрес изменился. Перейдите в каталог или воспользуйтесь поиском на главной странице.</p>
    <a href="<?= htmlspecialchars(PATH, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Вернуться на главную</a>
</main>
</body>
</html>
