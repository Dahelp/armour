<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
echo function_exists('opcache_reset') && opcache_reset() ? 'ok' : 'unavailable';
