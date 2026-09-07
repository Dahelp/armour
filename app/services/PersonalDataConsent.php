<?php

namespace app\services;

final class PersonalDataConsent
{
    public static function accepted(array $input): bool
    {
        return isset($input['privacy_accept']) && (string)$input['privacy_accept'] === '1';
    }

    public static function reject(array $input = []): void
    {
        $_SESSION['error'] = 'Подтвердите принятие Политики конфиденциальности и согласие на обработку персональных данных.';

        foreach (['password', 'confirm_password', 'filename_rekvizit', 'rekvizity'] as $sensitiveField) {
            unset($input[$sensitiveField]);
        }

        if ($input !== []) {
            $_SESSION['form_data'] = $input;
        }
    }
}
