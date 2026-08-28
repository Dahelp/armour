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
            'SELECT product_id, attribute_id, attribute_text
             FROM product_attribute
             WHERE product_id IN (' . \R::genSlots($productIds) . ')',
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
}
