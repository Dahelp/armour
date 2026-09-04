<?php

declare(strict_types=1);

namespace app\services;

final class ProductionMigrationService
{
    /** @return array<string, int> */
    public function run(string $redirectMap): array
    {
        $this->createRedirectTable();
        $indexesAdded = $this->addIndexes();
        $aliasesFixed = \R::exec(
            "UPDATE url_alias ua INNER JOIN attribute_group ag ON ag.url_params = ua.sef
             SET ua.urlid = ag.id WHERE ua.urlid = 0 AND ag.url_params <> ''"
        );

        $validation = (new LegacyUrlMapValidator())->validateCsv($redirectMap);
        if ($validation['errors'] !== []) {
            throw new \RuntimeException('Production-карта URL не прошла проверку: ' . implode('; ', $validation['errors']));
        }
        $redirectsImported = (new LegacyUrlRedirectRepository())->upsert($validation['rows']);

        return [
            'indexes_added' => $indexesAdded,
            'aliases_fixed' => $aliasesFixed,
            'redirects_imported' => $redirectsImported,
            'redirects_active' => (int)\R::getCell('SELECT COUNT(*) FROM legacy_url_redirect WHERE is_active = 1'),
        ];
    }

    private function createRedirectTable(): void
    {
        \R::exec(
            'CREATE TABLE IF NOT EXISTS legacy_url_redirect (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                source_path VARCHAR(191) NOT NULL,
                target_path VARCHAR(768) NOT NULL,
                status_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_legacy_url_redirect_source_path (source_path),
                KEY idx_legacy_url_redirect_target_path (target_path(191)),
                CONSTRAINT chk_legacy_url_redirect_status_code CHECK (status_code IN (301, 308))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function addIndexes(): int
    {
        $definitions = [
            ['url_alias', 'idx_url_alias_sef', 'ALTER TABLE url_alias ADD INDEX idx_url_alias_sef (sef(191))'],
            ['product', 'idx_product_category_listing', 'ALTER TABLE product ADD INDEX idx_product_category_listing (category_id, hide, stock_status_id, name(64))'],
            ['product', 'idx_product_alias_visibility', 'ALTER TABLE product ADD INDEX idx_product_alias_visibility (alias(191), hide)'],
            ['category', 'idx_category_parent', 'ALTER TABLE category ADD INDEX idx_category_parent (parent_id)'],
            ['category', 'idx_category_alias', 'ALTER TABLE category ADD INDEX idx_category_alias (alias(191))'],
            ['attribute_product', 'idx_attribute_product_filter', 'ALTER TABLE attribute_product ADD INDEX idx_attribute_product_filter (attr_id, product_id)'],
            ['attribute_product', 'idx_attribute_product_product', 'ALTER TABLE attribute_product ADD INDEX idx_attribute_product_product (product_id, attr_id)'],
            ['product_attribute', 'idx_product_attribute_product', 'ALTER TABLE product_attribute ADD INDEX idx_product_attribute_product (product_id, attribute_group_id, attribute_id)'],
            ['attribute_category', 'idx_attribute_category_category', 'ALTER TABLE attribute_category ADD INDEX idx_attribute_category_category (category_id, group_id)'],
            ['related_product', 'idx_related_product_forward', 'ALTER TABLE related_product ADD INDEX idx_related_product_forward (product_id, related_id)'],
            ['related_product', 'idx_related_product_reverse', 'ALTER TABLE related_product ADD INDEX idx_related_product_reverse (related_id, product_id)'],
            ['similar_product', 'idx_similar_product_forward', 'ALTER TABLE similar_product ADD INDEX idx_similar_product_forward (product_id, similar_id)'],
            ['similar_product', 'idx_similar_product_reverse', 'ALTER TABLE similar_product ADD INDEX idx_similar_product_reverse (similar_id, product_id)'],
            ['review_product', 'idx_review_product_product', 'ALTER TABLE review_product ADD INDEX idx_review_product_product (product_id, review_id)'],
            ['gallery', 'idx_gallery_product', 'ALTER TABLE gallery ADD INDEX idx_gallery_product (product_id)'],
            ['modification', 'idx_modification_product', 'ALTER TABLE modification ADD INDEX idx_modification_product (product_id)'],
            ['plagins_inseo', 'idx_inseo_lookup', 'ALTER TABLE plagins_inseo ADD INDEX idx_inseo_lookup (tip, category_id, hide)'],
        ];

        $added = 0;
        foreach ($definitions as [$table, $index, $sql]) {
            $exists = (int)\R::getCell(
                'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                [$table, $index]
            );
            if ($exists === 0) {
                \R::exec($sql);
                ++$added;
            }
        }
        return $added;
    }
}
