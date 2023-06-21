<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Relation\FileType\Processor;

use Amasty\ProductAttachment\Model\Filesystem\UploadFileData;

class FileLink extends TypeProcessor
{
    protected function isNeedToSaveFile(): bool
    {
        return false;
    }

    protected function getFilePath(UploadFileData $uploadFileData): string
    {
        return '';
    }
}
