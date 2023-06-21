<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Relation\FileType;

use Amasty\ProductAttachment\Model\Import\Relation\FileType\Processor\TypeProcessorInterface;

class TypeProcessorProvider
{
    /**
     * @var TypeProcessorInterface[]
     */
    private $processors;

    /**
     * @param array $fileTypes [ 'file_type' => [
     * 'code' => 'file', 'typeCode' => 0, 'processor' => ClassProcessor
     * ] ]
     */
    public function __construct(
        array $fileTypes = []
    ) {
        $this->initializeFileTypes($fileTypes);
    }

    /**
     * @return TypeProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    private function initializeFileTypes(array $fileTypes): void
    {
        foreach ($fileTypes as $type => $processor) {
            if (!$processor['processor'] instanceof TypeProcessorInterface) {
                throw new \LogicException(
                    sprintf('Import File type must implement %s', TypeProcessorInterface::class)
                );
            }
            $this->processors[$type] = $processor;
        }
    }
}
