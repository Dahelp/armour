<?php

declare(strict_types=1);

function assertCartMail(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
defined('APP') || define('APP', $root . '/app');
defined('TEMPLATE') || define('TEMPLATE', 'armour');
defined('PATH') || define('PATH', 'https://techtires.ru');

function renderCartMail(array $overrides): string
{
    $_SESSION['cart'] = [
        551 => [
            'name' => 'EK-1008 Фильтр <тест>',
            'qty' => 2,
            'price' => 460,
        ],
    ];
    $_SESSION['cart.qty'] = 2;
    $_SESSION['cart.sum'] = 920;
    $_SESSION['cart.currency'] = ['symbol_left' => '', 'symbol_right' => ' ₽'];

    $defaults = [
        'order_id' => 123,
        'ord' => ['inv' => 'SH000000123'],
        'namecomp' => 'ТехШина',
        'tell_site' => '+7 (925) 070-77-07',
        'uname' => 'Иван <Иванов>',
        'telefon' => '+7 900 000-00-00',
        'user_email' => 'buyer@example.com',
        'note' => 'Комментарий <проверка>',
        'date' => '2026-09-23 10:00:00',
        'dostavka_name' => '',
        'branch_name' => '',
        'address' => '',
        'transport_company' => '',
        'city_name' => '',
        'vid' => '',
        'compname' => '',
        'nds' => '',
        'dogovor' => '',
        'rekvizity_name' => '',
    ];

    extract(array_merge($defaults, $overrides), EXTR_SKIP);
    ob_start();
    require APP . '/views/' . TEMPLATE . '/mail/mail_order.php';
    return (string)ob_get_clean();
}

$pickup = renderCartMail([
    'dostavka_name' => 'Самовывоз',
    'branch_name' => 'г. Климовск',
    'vid' => 'Физическое лицо',
]);
assertCartMail(str_contains($pickup, 'Способ доставки'), 'Pickup mail must include delivery method.');
assertCartMail(str_contains($pickup, 'Пункт самовывоза'), 'Pickup mail must include branch.');
assertCartMail(str_contains($pickup, 'Физическое лицо'), 'Pickup mail must include client type.');
assertCartMail(str_contains($pickup, 'Иван &lt;Иванов&gt;'), 'Mail must escape customer name.');
assertCartMail(str_contains($pickup, 'EK-1008 Фильтр &lt;тест&gt;'), 'Mail must escape product name.');

$transport = renderCartMail([
    'dostavka_name' => 'Транспортная компания',
    'transport_company' => 'Деловые линии',
    'city_name' => 'Санкт-Петербург',
    'vid' => 'Физическое лицо',
]);
assertCartMail(str_contains($transport, 'Транспортная компания'), 'Transport mail must include transport company.');
assertCartMail(str_contains($transport, 'Санкт-Петербург'), 'Transport mail must include city.');

$courier = renderCartMail([
    'dostavka_name' => 'Курьер',
    'address' => 'Москва, ул. Тестовая, 1',
    'vid' => 'Физическое лицо',
]);
assertCartMail(str_contains($courier, 'Адрес'), 'Courier mail must include address label.');
assertCartMail(str_contains($courier, 'Москва, ул. Тестовая, 1'), 'Courier mail must include address value.');

$legalVat = renderCartMail([
    'dostavka_name' => 'Самовывоз',
    'branch_name' => 'г. Климовск',
    'vid' => 'Юридическое лицо',
    'nds' => 'с НДС',
    'dogovor' => 'Договор',
]);
assertCartMail(str_contains($legalVat, 'Юридическое лицо'), 'Legal mail must include client type.');
assertCartMail(str_contains($legalVat, 'с НДС'), 'Legal VAT mail must include VAT mode.');
assertCartMail(str_contains($legalVat, 'Договор'), 'Legal VAT mail must include supply terms.');

$legalNoVat = renderCartMail([
    'dostavka_name' => 'Транспортная компания',
    'transport_company' => 'ПЭК',
    'city_name' => 'Казань',
    'vid' => 'Юридическое лицо',
    'nds' => 'без НДС',
    'dogovor' => 'Счёт-договор',
    'rekvizity_name' => 'rekvizity.pdf',
]);
assertCartMail(str_contains($legalNoVat, 'без НДС'), 'Legal no-VAT mail must include VAT mode.');
assertCartMail(str_contains($legalNoVat, 'Счёт-договор'), 'Legal no-VAT mail must include supply terms.');
assertCartMail(str_contains($legalNoVat, 'rekvizity.pdf'), 'Mail must mention attached requisites file.');

echo "Cart mail template tests passed.\n";
