<?php

declare(strict_types=1);

use app\services\LegacyUrlMapValidator;
use app\services\LegacyUrlRedirectRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

$arguments = array_slice($argv, 1);
$apply = in_array('--apply', $arguments, true);
$filename = null;
foreach ($arguments as $argument) {
    if (!str_starts_with($argument, '--')) {
        $filename = $argument;
        break;
    }
}

if ($filename === null) {
    fwrite(STDERR, "Использование: php bin/import_legacy_redirects.php <map.csv> [--apply]\n");
    exit(2);
}

$validator = new LegacyUrlMapValidator();
$result = $validator->validateCsv($filename);
if ($result['errors'] !== []) {
    fwrite(STDERR, "Карта URL не прошла проверку:\n- " . implode("\n- ", $result['errors']) . "\n");
    exit(1);
}

if ($apply) {
    require dirname(__DIR__) . '/config/init.php';
}
printf("Проверено строк: %d. Ошибок: 0.\n", count($result['rows']));
if (!$apply) {
    echo "Dry-run завершён: база данных не изменена. Для импорта добавьте --apply.\n";
    exit(0);
}

\ishop\Db::instance();
$count = (new LegacyUrlRedirectRepository())->upsert($result['rows']);
printf("Импортировано или обновлено строк: %d.\n", $count);
