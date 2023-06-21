<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Relation\FileType\Processor;

use Amasty\ProductAttachment\Model\Filesystem\Directory;

class FilePath extends TypeProcessor
{
    protected function getTmpFileName(array $file): string
    {
        return Directory::DIRECTORY_CODES[Directory::IMPORT_FTP] . DIRECTORY_SEPARATOR . $file['filepath'];
    }
}
