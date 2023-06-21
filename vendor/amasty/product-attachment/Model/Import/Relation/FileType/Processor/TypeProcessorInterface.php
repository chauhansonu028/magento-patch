<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Relation\FileType\Processor;

use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFile;

interface TypeProcessorInterface
{
    public function processNewFile(array $file, ImportFile $importFile): void;

    public function processExistFile(ImportFile $file, array $existFile): void;
}
