<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Healthcheck;

use Amasty\ProductAttachment\Model\File\FileType\TypeProcessorProvider;

class InvalidLinksProvider
{
    public const ID = 'id';
    public const URL = 'url';
    public const RESPONSE = 'response';

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    /**
     * @var array
     */
    private $invalidLinks = [];

    public function __construct(
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    /**
     * @return array [
     * 'linkKey'=>['id'=>'fileId','url'=>'fileUrl','response'=>'statusCode: reasonPhrase'],
     * 'otherlinkKey'=>[...]
     * ]
     */
    public function getInvalidLinks(): array
    {
        if (empty($this->invalidLinks)) {
            $this->collect();
        }

        return $this->invalidLinks;
    }

    private function collect(): void
    {
        foreach ($this->typeProcessorProvider->getTypeCodes() as $typeCode => $type) {
            $invalidLinks = $this->typeProcessorProvider->getProcessorByType($typeCode)->collectInvalidLinks();
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $this->invalidLinks = array_merge($this->invalidLinks, $invalidLinks);
        }
    }
}
