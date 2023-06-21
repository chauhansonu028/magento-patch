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
use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFile;
use Magento\Framework\Filesystem\Io\File as Filesystem;

class File implements TypeProcessorInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    public function getFilePath(array $row): string
    {
        return Directory::DIRECTORY_CODES[Directory::IMPORT] . DIRECTORY_SEPARATOR
            . (int)$row[ImportFile::IMPORT_ID] . DIRECTORY_SEPARATOR
            . $row[FileInterface::FILE_PATH];
    }

    public function setFilePath(FileInterface $file, string $path): void
    {
        $pathInfo = $this->filesystem->getPathInfo($path);
        $baseName = !empty($pathInfo['basename']) ? $pathInfo['basename'] : '';
        $file->setData(
            RegistryConstants::FILE_KEY,
            [
                [
                    'file' => $path,
                    'name' => $baseName,
                    'tmp_name' => $baseName
                ]
            ]
        );
        $file->setLink('');
    }
}
