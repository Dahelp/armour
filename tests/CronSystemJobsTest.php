<?php

declare(strict_types=1);

namespace app\models {
    class AppModel {}
}

namespace {
    class R
    {
        public static array $jobs = [];

        public static function findOne(string $table, string $query, array $params): ?array
        {
            return self::$jobs[$params[0]] ?? null;
        }

        public static function exec(string $query, array $params): void
        {
            self::$jobs[$params[2]] = $params;
        }
    }

    require dirname(__DIR__) . '/app/models/admin/Cron.php';

    \app\models\admin\Cron::ensureSystemJobs();
    foreach (['refresh-filtry' => 'filtrs.csv', 'refresh-kamery' => 'kamery.csv'] as $name => $file) {
        $job = R::$jobs[$name] ?? [];
        if (($job[1] ?? '') !== 'https://its50.ru/xls/nalichie/' . $file || ($job[3] ?? '') !== $file || ($job[4] ?? '') !== 'show') {
            throw new RuntimeException('Missing or incorrectly configured feed job: ' . $name);
        }
    }

    R::$jobs['refresh-kamery'][1] = 'https://example.com/custom.csv';
    $existing = R::$jobs;
    \app\models\admin\Cron::ensureSystemJobs();
    if (R::$jobs !== $existing || count(R::$jobs) !== 4) {
        throw new RuntimeException('Provisioning must preserve existing jobs and avoid duplicates.');
    }

    echo "System cron provisioning checks passed.\n";
}
