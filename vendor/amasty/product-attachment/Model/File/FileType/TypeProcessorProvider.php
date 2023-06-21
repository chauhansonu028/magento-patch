<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileType;

use Amasty\ProductAttachment\Model\File\FileType\Processor\TypeProcessorInterface;

class TypeProcessorProvider
{
    /**
     * @var TypeProcessorInterface[]
     */
    private $processors;

    /**
     * @var string[]
     */
    private $fileTypeCodes;

    /**
     * @var string[]
     */
    private $options;

    /**
     * @param array $fileTypes [ 'file_type' => [
     * 'code' => 'file', 'typeCode' => 0, 'optionLabel' => 'File', 'processor' => TypeProcessorInterface
     * ] ]
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

    public function getTypeCodes(): array
    {
        return $this->fileTypeCodes;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    private function initializeFileTypes(array $fileTypes): void
    {
        foreach ($fileTypes as $fileType) {
            if (!$fileType['processor'] instanceof TypeProcessorInterface) {
                throw new \LogicException(
                    sprintf('File type must implement %s', TypeProcessorInterface::class)
                );
            }
            $this->processors[$fileType['typeCode']] = $fileType['processor'];
            $this->fileTypeCodes[$fileType['typeCode']] = $fileType['code'];
            $this->options[$fileType['typeCode']] = $fileType['optionLabel'];
        }
    }
}
