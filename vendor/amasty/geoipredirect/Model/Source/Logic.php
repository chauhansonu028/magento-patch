<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Model\Source;

class Logic implements \Magento\Framework\Data\OptionSourceInterface
{
    public const SPECIFIED_URLS = 1;
    public const EXCEPT_URLS = 2;
    public const HOMEPAGE_ONLY = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EXCEPT_URLS, 'label' => __('All Except Specified URLs')],
            ['value' => self::SPECIFIED_URLS, 'label' => __('Specified URLs')],
            ['value' => self::HOMEPAGE_ONLY, 'label' => __('Redirect From Home Page Only')]
        ];
    }
}
