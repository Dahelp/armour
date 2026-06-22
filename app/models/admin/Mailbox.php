<?php

namespace app\models;

use ishop\App;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailbox extends AppModel {

    public static function sendMail(){
        // Create the Transport
        $transport = (new Swift_SmtpTransport(App::$app->getProperty('smtp_host'), App::$app->getProperty('smtp_port'), App::$app->getProperty('smtp_protocol')))
            ->setUsername(App::$app->getProperty('smtp_login'))
            ->setPassword(App::$app->getProperty('smtp_password'))
        ;
        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        $message_client = (new Swift_Message("Вы совершили заказ №{$order_id} на сайте " . App::$app->getProperty('shop_name')))
            ->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
            ->setTo($user_email)
            ->setBody($body, 'text/html')
        ;

        $message_admin = (new Swift_Message("Сделан заказ №{$order_id}"))
            ->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
            ->setTo(App::$app->getProperty('admin_email'))
            ->setBody($body, 'text/html')
        ;

        // Send the message
        $result = $mailer->send($message_client);
        $result = $mailer->send($message_admin);

        $_SESSION['success'] = 'Письмо отправлено!';
    }

}