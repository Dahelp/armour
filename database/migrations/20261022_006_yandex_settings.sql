-- Apply once to install the supplied counter and Webmaster verification code.
START TRANSACTION;

UPDATE options SET alt_name = 'option_yandex_metrika', znachenie = '113728633',
    name_op = 'Яндекс Метрика (номер счётчика)', placeholder = 'Только номер счётчика', tag = 'input', tip = 'Основные'
WHERE alt_name = 'option_yandex_metrika' OR name_op LIKE '%Метрика%';

INSERT INTO options (name_op, alt_name, znachenie, placeholder, tag, tip, size)
SELECT 'Яндекс Метрика (номер счётчика)', 'option_yandex_metrika', '113728633', 'Только номер счётчика', 'input', 'Основные', 255
WHERE NOT EXISTS (SELECT 1 FROM options WHERE alt_name = 'option_yandex_metrika');

UPDATE options SET znachenie = '11e51ffcde490daf'
WHERE alt_name = 'option_yandex_verification';

INSERT INTO options (name_op, alt_name, znachenie, placeholder, tag, tip, size)
SELECT 'Яндекс Вебмастер (код подтверждения)', 'option_yandex_verification', '11e51ffcde490daf', 'Только код из content, без метатега', 'input', 'Основные', 255
WHERE NOT EXISTS (SELECT 1 FROM options WHERE alt_name = 'option_yandex_verification');

COMMIT;

