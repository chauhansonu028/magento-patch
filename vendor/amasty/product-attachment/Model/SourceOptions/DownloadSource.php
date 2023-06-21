<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\SourceOptions;

use Magento\Framework\Option\ArrayInterface;

class DownloadSource implements ArrayInterface
{
    public const PRODUCT = 1;
    public const CATEGORY = 2;
    public const ORDER = 3;
    public const OTHER = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $widgetType => $label) {
            $optionArray[] = ['value' => $widgetType, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::PRODUCT => __('Product'),
            self::CATEGORY => __('Category'),
            self::ORDER => __('Order'),
            self::OTHER => __('Other'),
        ];
    }
}
