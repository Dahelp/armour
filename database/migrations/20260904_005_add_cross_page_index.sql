-- Public cross pages resolve by cross_abbreviated_name and then join product.
-- Run once; CrossIndexService applies the same guarded change in production.
ALTER TABLE `plagins_cross`
    ADD INDEX `idx_cross_public_alias` (`cross_abbreviated_name`(191), `product_id`);
