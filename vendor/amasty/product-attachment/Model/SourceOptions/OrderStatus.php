<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\SourceOptions;

class OrderStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    public function toOptionArray()
    {
        //remove Please Select option
        return array_slice(parent::toOptionArray(), 1);
    }
}
