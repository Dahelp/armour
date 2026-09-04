<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function coreAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

\ishop\Router::reset();
\ishop\Router::add('^catalog/(?P<alias>[a-z0-9-]+)/?$', [
    'controller' => 'Category',
    'action' => 'view',
    'canonical_url' => 'https://techtires.ru/catalog-vil',
]);

coreAssert(\ishop\Router::matchRoute('catalog/catalog-vil'), 'A valid route must match.');
$route = \ishop\Router::getRoute();
coreAssert($route['controller'] === 'Category', 'The controller was not normalised.');
coreAssert($route['alias'] === 'catalog-vil', 'The alias was not extracted.');
coreAssert($route['canonical_url'] === 'https://techtires.ru/catalog-vil', 'Canonical metadata was lost.');

coreAssert(!\ishop\Router::matchRoute('catalog/catalog-vil/extra'), 'A partial route must not match.');
coreAssert(\ishop\Router::getRoute() === [], 'A failed match must not retain the previous route.');

coreAssert(
    \app\services\LegacyUrlRedirector::normalisePath('/OLD-PRODUCT.HTML/') === 'old-product.html',
    'Legacy .html URLs must be normalised without losing the extension.'
);
coreAssert(
    \app\services\LegacyUrlRedirector::normalisePath('/catalog\\catalog-vil/') === 'catalog/catalog-vil',
    'Legacy path separators must be normalised.'
);
coreAssert(
    \app\services\LegacyUrlRedirector::normalisePath('../admin') === '',
    'Traversal-like paths must be rejected.'
);

coreAssert(\ishop\ErrorHandler::normaliseStatusCode(404) === 404, 'HTTP 404 must be preserved.');
coreAssert(\ishop\ErrorHandler::normaliseStatusCode(419) === 419, 'HTTP 419 must be preserved.');
coreAssert(\ishop\ErrorHandler::normaliseStatusCode(0) === 500, 'Missing exception codes must become 500.');
coreAssert(\ishop\ErrorHandler::normaliseStatusCode(700) === 500, 'Invalid HTTP codes must become 500.');

echo "Core routing and error handling checks passed.\n";
