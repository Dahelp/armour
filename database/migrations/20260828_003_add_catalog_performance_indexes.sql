-- Indexes for storefront queries observed in CategoryController,
-- ProductController, filter widgets and recommendation blocks.
-- Each statement is conditional, so the migration can be rerun safely.

DELIMITER $$

DROP PROCEDURE IF EXISTS add_index_if_missing$$
CREATE PROCEDURE add_index_if_missing(
    IN target_table VARCHAR(64),
    IN target_index VARCHAR(64),
    IN alter_statement TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = target_table
          AND index_name = target_index
    ) THEN
        SET @index_sql = alter_statement;
        PREPARE index_statement FROM @index_sql;
        EXECUTE index_statement;
        DEALLOCATE PREPARE index_statement;
    END IF;
END$$

CALL add_index_if_missing('product', 'idx_product_category_listing',
    'ALTER TABLE `product` ADD INDEX `idx_product_category_listing` (`category_id`, `hide`, `stock_status_id`, `name`(64))')$$
CALL add_index_if_missing('product', 'idx_product_alias_visibility',
    'ALTER TABLE `product` ADD INDEX `idx_product_alias_visibility` (`alias`(191), `hide`)')$$
CALL add_index_if_missing('category', 'idx_category_parent',
    'ALTER TABLE `category` ADD INDEX `idx_category_parent` (`parent_id`)')$$
CALL add_index_if_missing('category', 'idx_category_alias',
    'ALTER TABLE `category` ADD INDEX `idx_category_alias` (`alias`(191))')$$

CALL add_index_if_missing('attribute_product', 'idx_attribute_product_filter',
    'ALTER TABLE `attribute_product` ADD INDEX `idx_attribute_product_filter` (`attr_id`, `product_id`)')$$
CALL add_index_if_missing('attribute_product', 'idx_attribute_product_product',
    'ALTER TABLE `attribute_product` ADD INDEX `idx_attribute_product_product` (`product_id`, `attr_id`)')$$
CALL add_index_if_missing('product_attribute', 'idx_product_attribute_product',
    'ALTER TABLE `product_attribute` ADD INDEX `idx_product_attribute_product` (`product_id`, `attribute_group_id`, `attribute_id`)')$$
CALL add_index_if_missing('attribute_category', 'idx_attribute_category_category',
    'ALTER TABLE `attribute_category` ADD INDEX `idx_attribute_category_category` (`category_id`, `group_id`)')$$

CALL add_index_if_missing('related_product', 'idx_related_product_forward',
    'ALTER TABLE `related_product` ADD INDEX `idx_related_product_forward` (`product_id`, `related_id`)')$$
CALL add_index_if_missing('related_product', 'idx_related_product_reverse',
    'ALTER TABLE `related_product` ADD INDEX `idx_related_product_reverse` (`related_id`, `product_id`)')$$
CALL add_index_if_missing('similar_product', 'idx_similar_product_forward',
    'ALTER TABLE `similar_product` ADD INDEX `idx_similar_product_forward` (`product_id`, `similar_id`)')$$
CALL add_index_if_missing('similar_product', 'idx_similar_product_reverse',
    'ALTER TABLE `similar_product` ADD INDEX `idx_similar_product_reverse` (`similar_id`, `product_id`)')$$

CALL add_index_if_missing('review_product', 'idx_review_product_product',
    'ALTER TABLE `review_product` ADD INDEX `idx_review_product_product` (`product_id`, `review_id`)')$$
CALL add_index_if_missing('gallery', 'idx_gallery_product',
    'ALTER TABLE `gallery` ADD INDEX `idx_gallery_product` (`product_id`)')$$
CALL add_index_if_missing('modification', 'idx_modification_product',
    'ALTER TABLE `modification` ADD INDEX `idx_modification_product` (`product_id`)')$$
CALL add_index_if_missing('plagins_inseo', 'idx_inseo_lookup',
    'ALTER TABLE `plagins_inseo` ADD INDEX `idx_inseo_lookup` (`tip`, `category_id`, `hide`)')$$

DROP PROCEDURE add_index_if_missing$$
DELIMITER ;
