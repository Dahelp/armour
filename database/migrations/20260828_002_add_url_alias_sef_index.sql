-- Exact lookup by url_alias.sef runs on every public request.
-- The conditional statement keeps this migration safe if an equivalent
-- named index has already been installed in a target environment.

SET @index_exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'url_alias'
      AND index_name = 'idx_url_alias_sef'
);

SET @statement = IF(
    @index_exists = 0,
    'ALTER TABLE `url_alias` ADD INDEX `idx_url_alias_sef` (`sef`(191))',
    'SELECT 1'
);

PREPARE seo_index_statement FROM @statement;
EXECUTE seo_index_statement;
DEALLOCATE PREPARE seo_index_statement;
