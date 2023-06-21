<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MetaRobots implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'index, follow',
                'label' => __('INDEX, FOLLOW'),
            ],
            [
                'value' => 'noindex, follow',
                'label' => __('NOINDEX, FOLLOW'),
            ],
            [
                'value' => 'index, nofollow',
                'label' => __('INDEX, NOFOLLOW'),
            ],
            [
                'value' => 'noindex, nofollow',
                'label' => __('NOINDEX, NOFOLLOW'),
            ],
        ];
    }
}
