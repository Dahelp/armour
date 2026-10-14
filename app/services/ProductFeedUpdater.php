<?php

namespace app\services;

final class ProductFeedUpdater
{
    public function update(string $sourceUrl): array
    {
        $sourceUrl = trim($sourceUrl);
        if (!filter_var($sourceUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($sourceUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new \RuntimeException('Укажите корректный HTTP(S)-адрес файла с остатками.');
        }

        $curl = curl_init($sourceUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'TechTires product updater/1.0',
        ]);
        $contents = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if (!is_string($contents) || $contents === '' || $status < 200 || $status >= 300) {
            throw new \RuntimeException($error !== '' ? 'Не удалось скачать файл: ' . $error : 'Источник вернул HTTP ' . $status . '.');
        }

        // Supplier feeds can be encoded as Windows-1251. Line boundaries are
        // ASCII bytes, so parsing them must not require valid UTF-8 input.
        $lines = preg_split('/\r\n|\n|\r/', $contents) ?: [];
        if (count($lines) < 2) {
            throw new \RuntimeException('Файл с остатками пуст или не содержит строк товаров.');
        }

        $branches = \R::getAll('SELECT branch_id, tbl FROM branch_office');
        $updated = 0;
        $notFound = 0;
        $invalid = 0;

        \R::begin();
        try {
            foreach (array_slice($lines, 1) as $line) {
                if (trim($line) === '') continue;
                $fields = str_getcsv($line, ';');
                if (count($fields) < 17) {
                    $invalid++;
                    continue;
                }
                [$article, $price, $optPrice, $free, $reserve, $klimovsk, $reserveKlimovsk, $krasnodar, $reserveKrasnodar, $voronezh, $reserveVoronezh, $spb, $reserveSpb, $ekaterinburg, $reserveEkaterinburg, $arrivalQuantity, $arrivalDate] = array_map('trim', array_slice($fields, 0, 17));
                $article = ltrim($article, '0');
                if ($article === '') {
                    $invalid++;
                    continue;
                }

                $product = \R::findOne('product', 'article = ?', [$article]);
                if (!$product) {
                    $notFound++;
                    continue;
                }

                $quantity = $this->number($free) + $this->number($reserve);
                \R::exec(
                    'UPDATE product SET price = ?, data_edit_price = ?, opt_price = ?, stock_status_id = ?, quantity = ? WHERE id = ?',
                    [$this->number($price), date('Y-m-d'), $this->number($optPrice), $quantity != 0 ? 1 : 0, $quantity, $product->id]
                );

                $stockValues = [
                    'f' => $klimovsk,
                    'rkl' => $reserveKlimovsk,
                    'g' => $krasnodar,
                    'rkr' => $reserveKrasnodar,
                    'k' => $voronezh,
                    'rv' => $reserveVoronezh,
                    'l' => $spb,
                    'rspb' => $reserveSpb,
                    'ek' => $ekaterinburg,
                    'ekr' => $reserveEkaterinburg,
                    't' => $arrivalQuantity,
                ];
                foreach ($branches as $branch) {
                    $key = (string)$branch['tbl'];
                    if (!array_key_exists($key, $stockValues)) continue;
                    $stockQuantity = $this->number($stockValues[$key]);
                    $existing = \R::findOne('in_stock', 'product_id = ? AND branch_id = ?', [$product->id, $branch['branch_id']]);
                    if ($existing) {
                        \R::exec('UPDATE in_stock SET quantity = ?, date_scheduling = ? WHERE product_id = ? AND branch_id = ?', [$stockQuantity, $arrivalDate ?: '0000-00-00', $product->id, $branch['branch_id']]);
                    } else {
                        \R::exec('INSERT INTO in_stock (branch_id, product_id, quantity, date_scheduling) VALUES (?, ?, ?, ?)', [$branch['branch_id'], $product->id, $stockQuantity, $arrivalDate ?: '0000-00-00']);
                    }
                }
                $updated++;
            }
            \R::commit();
        } catch (\Throwable $exception) {
            \R::rollback();
            throw $exception;
        }

        if ($updated === 0) {
            throw new \RuntimeException('В файле не найдено ни одного артикула, совпадающего с товарами сайта.');
        }

        return ['updated' => $updated, 'not_found' => $notFound, 'invalid' => $invalid];
    }

    private function number(string $value): float
    {
        $value = str_replace([' ', ','], ['', '.'], $value);
        return is_numeric($value) ? (float)$value : 0.0;
    }
}
