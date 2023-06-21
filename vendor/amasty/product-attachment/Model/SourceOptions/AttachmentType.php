<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\SourceOptions;

use Amasty\ProductAttachment\Model\File\FileType\TypeProcessorProvider;
use Magento\Framework\Option\ArrayInterface;

class AttachmentType implements ArrayInterface
{
    public const FILE = 0;
    public const LINK = 1;

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    public function __construct(
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
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
    public function toArray(): array
    {
        return $this->typeProcessorProvider->getOptions();
    }
}
