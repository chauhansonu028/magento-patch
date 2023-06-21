<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Behaviors\FileType\Processor;

use Amasty\ProductAttachment\Api\Data\FileInterface;

interface TypeProcessorInterface
{
    public function getFilePath(array $row): string;

    public function setFilePath(FileInterface $file, string $path): void;
}
