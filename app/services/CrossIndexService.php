<?php
declare(strict_types=1);

namespace app\services;

final class CrossIndexService
{
    public function ensure(): bool
    {
        $exists=(int)\R::getCell(
            'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=? AND index_name=?',
            ['plagins_cross','idx_cross_public_alias']
        );
        if($exists>0)return false;
        \R::exec('ALTER TABLE plagins_cross ADD INDEX idx_cross_public_alias (cross_abbreviated_name(191), product_id)');
        return true;
    }
}
