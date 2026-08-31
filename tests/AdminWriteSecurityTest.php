<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\services\RelationWriter;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$rejected = false;
try {
    (new RelationWriter())->replace('attribute_category; DROP TABLE category', 'group_id', 1, 'category_id', []);
} catch (InvalidArgumentException) {
    $rejected = true;
}
assertTrue($rejected, 'RelationWriter must reject unsafe SQL identifiers.');

$root = dirname(__DIR__);
$options = file_get_contents($root . '/app/controllers/admin/OptionsController.php');
$mailbox = file_get_contents($root . '/app/controllers/admin/MailboxController.php');
$attribute = file_get_contents($root . '/app/controllers/admin/AttributeController.php');
$filters = file_get_contents($root . '/app/controllers/admin/FiltrsController.php');
$migration = file_get_contents($root . '/database/migrations/20260831_004_fix_attribute_group_url_alias_ids.sql');

assertTrue(str_contains($options, 'UPDATE options SET znachenie = ? WHERE option_id = ?'), 'Option writes must use bindings.');
assertTrue(str_contains($mailbox, 'UPDATE mails_imap SET is_seen = ? WHERE message_id = ?'), 'Mailbox writes must use bindings.');
assertTrue(!str_contains($attribute, 'attribute_text=\'".$'), 'CSV attribute values must not be concatenated into SQL.');
assertTrue(!str_contains($filters, "findLast('attribute_group')"), 'Filter edits must never target the last group.');
assertTrue(str_contains($filters, 'save($currentSef, $currentController, (int)$id)'), 'Group aliases must use the real group id.');
assertTrue(substr_count($filters, '(new RelationWriter())->replace') >= 2, 'Filter category relations must use the safe writer.');
assertTrue(str_contains($migration, 'SET ua.urlid = ag.id'), 'Legacy group aliases need an id repair migration.');

echo "Admin write security tests passed.\n";
