<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Layout implements OptionSourceInterface
{
    const ONE_COLUMN_LAYOUT = 'one-column';
    const TWO_COLUMNS_LEFT_LAYOUT = 'two-columns-left';
    const TWO_COLUMNS_RIGHT_LAYOUT = 'two-columns-right';
    const THREE_COLUMNS_LAYOUT  = 'three-columns';

    public function toOptionArray(): array
    {
        return $this->getDesktopOptions();
    }

    public function getDesktopOptions(): array
    {
        return array_merge(
            $this->getMobileOptions(),
            [
                ['value' => self::ONE_COLUMN_LAYOUT, 'label' => __("One Column")],
                ['value' => self::THREE_COLUMNS_LAYOUT, 'label' => __("Three Columns")],
            ]
        );
    }

    public function getMobileOptions(): array
    {
        return [
            ['value' => self::TWO_COLUMNS_LEFT_LAYOUT, 'label' => __("Two Columns and Left Sidebar")],
            ['value' => self::TWO_COLUMNS_RIGHT_LAYOUT, 'label' => __("Two Columns and Right Sidebar")]
        ];
    }
}
