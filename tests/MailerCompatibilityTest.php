<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function mailAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$transport = (new Swift_SmtpTransport('smtp.example.test', 587, 'tls'))
    ->setUsername('mailer@example.test')
    ->setPassword('p@ss word');

mailAssert(
    $transport->dsn() === 'smtp://mailer%40example.test:p%40ss%20word@smtp.example.test:587?require_tls=true',
    'SMTP credentials must be safely encoded in the DSN.'
);

$message = (new Swift_Message('Тестовое письмо'))
    ->setFrom(['mailer@example.test' => 'TechTires'])
    ->setTo(['customer@example.test' => 'Клиент'])
    ->setReplyTo('support@example.test')
    ->setBody('<strong>Проверка</strong>', 'text/html');
$message->attach(Swift_Attachment::fromPath(__FILE__)->setFilename('test-attachment.php'));

$email = $message->email();
mailAssert($email->getSubject() === 'Тестовое письмо', 'The subject was not preserved.');
mailAssert($email->getHtmlBody() === '<strong>Проверка</strong>', 'The HTML body was not preserved.');
mailAssert($email->getFrom()[0]->getAddress() === 'mailer@example.test', 'The sender was not preserved.');
mailAssert($email->getTo()[0]->getAddress() === 'customer@example.test', 'The recipient was not preserved.');
mailAssert($email->getReplyTo()[0]->getAddress() === 'support@example.test', 'Reply-To was not preserved.');
mailAssert($email->getAttachments()[0]->getFilename() === 'test-attachment.php', 'The attachment was not preserved.');

echo "Symfony Mailer compatibility checks passed.\n";
