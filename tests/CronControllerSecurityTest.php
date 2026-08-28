<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/environment.php';
require dirname(__DIR__) . '/vendor/autoload.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

putenv('CRON_TOKEN=test-cron-secret');

$reflection = new ReflectionClass(app\controllers\CronController::class);
$controller = $reflection->newInstanceWithoutConstructor();
$authorise = $reflection->getMethod('authoriseRequest');
$normaliseId = $reflection->getMethod('normaliseCronId');

$_SESSION = [];
$_GET = ['token' => 'invalid'];

try {
    $authorise->invoke($controller);
    throw new RuntimeException('Invalid cron token was accepted.');
} catch (Throwable $exception) {
    assertTrue($exception->getCode() === 403, 'Invalid token must return 403.');
}

$_GET = ['token' => 'test-cron-secret'];
$authorise->invoke($controller);

$_GET = ['id' => '42'];
$normaliseId->invoke($controller);
assertTrue($_GET['id'] === 42, 'Cron ID must be normalised to an integer.');

$_GET = ['id' => '42 OR 1=1'];

try {
    $normaliseId->invoke($controller);
    throw new RuntimeException('Invalid cron ID was accepted.');
} catch (Throwable $exception) {
    assertTrue($exception->getCode() === 400, 'Invalid cron ID must return 400.');
}

echo "Cron controller security checks passed.\n";
