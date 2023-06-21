<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Config\Source\Fonts;

class FontType
{
    public const DEFAULT = 'default';
    public const GOOGLE = 'google';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => self::DEFAULT, 'label' => __('Default')],
            ['value' => self::GOOGLE, 'label' => __('Google')],
        ];

        return $options;
    }
}
