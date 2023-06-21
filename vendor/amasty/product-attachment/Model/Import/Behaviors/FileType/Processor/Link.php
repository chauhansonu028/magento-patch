<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Behaviors\FileType\Processor;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFile;

class Link implements TypeProcessorInterface
{
    public function getFilePath(array $row): string
    {
        return $row[ImportFile::LINK] ?? '';
    }

    public function setFilePath(FileInterface $file, string $path): void
    {
        $file->setData(RegistryConstants::FILE_KEY, []);
        $file->setLink($path);
    }
}
