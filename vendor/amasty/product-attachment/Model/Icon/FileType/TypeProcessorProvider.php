<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Icon\FileType;

use Amasty\ProductAttachment\Model\Icon\FileType\Processor\TypeProcessorInterface;

class TypeProcessorProvider
{
    /**
     * @var TypeProcessorInterface[]
     */
    private $processors;

    /**
     * @param array $fileTypes [ 'file_type' => ['typeCode' => 0, 'processor' => ProcessorClass ] ]
     */
    public function __construct(
        array $fileTypes = []
    ) {
        $this->initializeFileTypes($fileTypes);
    }

    public function getProcessorByType(int $type): TypeProcessorInterface
    {
        return $this->processors[$type];
    }

    private function initializeFileTypes(array $fileTypes): void
    {
        foreach ($fileTypes as $fileType) {
            if (!$fileType['processor'] instanceof TypeProcessorInterface) {
                throw new \LogicException(
                    sprintf('Icon file type must implement %s', TypeProcessorInterface::class)
                );
            }
            $this->processors[$fileType['typeCode']] = $fileType['processor'];
        }
    }
}
