<?php

declare(strict_types=1);

namespace app\services;

final class CatalogListingLoader
{
    /**
     * @param iterable<array|\ArrayAccess> $products
     * @return array{0: array<int, array<int, string>>, 1: array<int, array>}
     */
    public function load(iterable $products): array
    {
        $productIds = [];
        $brandIds = [];
        foreach ($products as $product) {
            $productIds[] = (int)$product['id'];
            if ((int)$product['brand_id'] > 0) {
                $brandIds[] = (int)$product['brand_id'];
            }
        }

        $productIds = array_values(array_unique(array_filter($productIds)));
        $brandIds = array_values(array_unique(array_filter($brandIds)));

        $attributeRows = $productIds === [] ? [] : \R::getAll(
            'SELECT pa.product_id, pa.attribute_id, pa.attribute_text, a.attribute_name
             FROM product_attribute pa
             INNER JOIN attribute a ON a.id = pa.attribute_id
             WHERE pa.product_id IN (' . \R::genSlots($productIds) . ') ORDER BY a.attribute_position',
            $productIds
        );
        $brandRows = $brandIds === [] ? [] : \R::getAll(
            'SELECT id, name FROM brand WHERE id IN (' . \R::genSlots($brandIds) . ')',
            $brandIds
        );

        return [$this->mapAttributes($attributeRows), $this->mapBrands($brandRows)];
    }

    /** @return array<int, array<int, string>> */
    public function mapAttributes(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['product_id']][(int)$row['attribute_id']] = (string)$row['attribute_text'];
            if (!empty($row['attribute_name']) && trim((string)$row['attribute_text']) !== '') {
                $result[(int)$row['product_id']]['_summary'][] = ['label' => (string)$row['attribute_name'], 'value' => (string)$row['attribute_text']];
            }
        }

        return $result;
    }

    /** @return array<int, array> */
    public function mapBrands(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['id']] = $row;
        }

        return $result;
    }

    /** Stable comparison fields; never replace a missing tire model with an unrelated dimension. */
    public static function cardSummary(array $attributes, bool $hideSize = false): array
    {
        $rows = $attributes['_summary'] ?? [];
        $byLabel = [];
        foreach ($rows as $row) $byLabel[mb_strtolower(trim($row['label']), 'UTF-8')] = $row;
        if (isset($byLabel['размер шины'])) {
            $summary = [];
            foreach ($hideSize ? ['модель', 'слойность (pr)'] : ['размер шины', 'модель', 'слойность (pr)'] as $label) {
                if (isset($byLabel[$label])) $summary[] = $byLabel[$label];
            }
            return $summary;
        }
        return array_slice($rows, 0, 2);
    }
}
