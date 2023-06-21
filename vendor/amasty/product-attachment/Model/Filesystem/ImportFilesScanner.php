<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

class ImportFilesScanner
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        Filesystem $filesystem,
        File $file
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->file = $file;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $result = [];

        $folders = $this->mediaDirectory->read(Directory::DIRECTORY_CODES[Directory::IMPORT_FTP]);
        foreach ($folders as $file) {
            if ($this->mediaDirectory->isFile($file)) {
                $result[] = $this->file->getPathInfo($file)['basename'] ?? '';
            }
        }

        return $result;
    }
}
