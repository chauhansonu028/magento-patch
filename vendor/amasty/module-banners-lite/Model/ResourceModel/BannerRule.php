<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\ResourceModel;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BannerRule extends AbstractDb
{
    public const TABLE_NAME = 'amasty_banners_lite_rule';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BannerRuleInterface::ENTITY_ID);
    }
}
