<?php

namespace app\controllers;

class InformationController extends AppController
{
    private const PAGES = [
        'delivery' => [
            'title' => 'Доставка и оплата',
            'description' => 'Способы оплаты и доставки шин, дисков и фильтров для спецтехники по России.',
            'canonical' => '/dostavka',
        ],
        'company' => [
            'title' => 'О компании «ТехШина»',
            'description' => 'ТехШина — поставщик промышленных шин, дисков и фильтров для специальной техники.',
            'canonical' => '/comp',
        ],
        'contacts' => [
            'title' => 'Контакты',
            'description' => 'Телефоны, электронная почта, адрес склада и реквизиты компании «ТехШина».',
            'canonical' => '/contacts',
        ],
        'privacy' => [
            'title' => 'Политика конфиденциальности',
            'description' => 'Политика обработки и защиты персональных данных пользователей сайта techtires.ru.',
            'canonical' => '/politika-konfidencialnosti',
        ],
        'consent' => [
            'title' => 'Согласие на обработку персональных данных',
            'description' => 'Согласие пользователя сайта techtires.ru на обработку персональных данных.',
            'canonical' => '/soglasie-na-obrabotku-personalnyh-dannyh',
        ],
        'cookies' => [
            'title' => 'Политика использования файлов cookie',
            'description' => 'Информация об использовании файлов cookie на сайте techtires.ru.',
            'canonical' => '/politika-fajlov-cookie',
        ],
        'terms' => [
            'title' => 'Пользовательское соглашение',
            'description' => 'Правила использования сайта techtires.ru и размещённой на нём информации.',
            'canonical' => '/polzovatelskoe-soglashenie',
        ],
    ];

    public function deliveryAction(): void { $this->show('delivery'); }
    public function companyAction(): void { $this->show('company'); }
    public function contactsAction(): void { $this->show('contacts'); }
    public function privacyAction(): void { $this->show('privacy'); }
    public function consentAction(): void { $this->show('consent'); }
    public function cookiesAction(): void { $this->show('cookies'); }
    public function termsAction(): void { $this->show('terms'); }

    private function show(string $key): void
    {
        $page = self::PAGES[$key];
        $this->view = 'view';
        $this->setMeta(
            $page['title'],
            $page['description'],
            '',
            'ТехШина',
            PATH . '/images/logo-techtires.svg',
            PATH . $page['canonical']
        );
        $this->set(compact('page', 'key'));
    }
}
