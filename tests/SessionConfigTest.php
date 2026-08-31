<?php

declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/public/index.php';

require dirname(__DIR__) . '/config/init.php';

if (ini_get('session.use_strict_mode') !== '1'
    || ini_get('session.cookie_httponly') !== '1'
    || strtolower((string)ini_get('session.cookie_samesite')) !== 'lax'
) {
    throw new RuntimeException('Secure session defaults are not active.');
}

echo "Session configuration checks passed.\n";
