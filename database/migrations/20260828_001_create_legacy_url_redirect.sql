CREATE TABLE IF NOT EXISTS `legacy_url_redirect` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `source_path` VARCHAR(191) NOT NULL,
    `target_path` VARCHAR(768) NOT NULL,
    `status_code` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_legacy_url_redirect_source_path` (`source_path`),
    KEY `idx_legacy_url_redirect_target_path` (`target_path`(191)),
    CONSTRAINT `chk_legacy_url_redirect_status_code` CHECK (`status_code` IN (301, 308))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
