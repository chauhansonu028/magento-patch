<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Model\Source;

class PopupType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const NOTIFICATION = 0;
    public const CONFIRMATION = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NOTIFICATION, 'label' => __('Notification Popup')],
            ['value' => self::CONFIRMATION, 'label' => __('Confirmation Popup')]
        ];
    }
}
