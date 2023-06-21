<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileType\Processor;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileType\FrontendUrlGenerator;
use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Filesystem\File as FileSystemFile;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;

class File implements TypeProcessorInterface
{
    /**
     * @var FileSystemFile
     */
    private $file;

    /**
     * @var FrontendUrlGenerator
     */
    private $frontendUrl;

    public function __construct(
        FileSystemFile $file,
        FrontendUrlGenerator $frontendUrl
    ) {
        $this->file = $file;
        $this->frontendUrl = $frontendUrl;
    }

    public function addFrontendUrl(FileInterface $file, array $params): void
    {
        $this->frontendUrl->addUrl($file, $params);
    }

    public function updateFile(FileInterface $file, bool $checkExtension): FileInterface
    {
        $data = $file->getData();
        if (isset($data[RegistryConstants::FILE_KEY]) && is_array($data[RegistryConstants::FILE_KEY])) {
            if (isset($data[RegistryConstants::FILE_KEY][0]['name'])
                && isset($data[RegistryConstants::FILE_KEY][0]['tmp_name'])
            ) {
                $uploadFileData = $this->file->getUploadFileData();
                $uploadFileData->setTmpFileName($data[RegistryConstants::FILE_KEY][0]['file']);
                if ($this->file->save(
                    $uploadFileData,
                    Directory::ATTACHMENT,
                    $checkExtension
                )) {
                    $data[FileInterface::FILE_PATH] = $uploadFileData->getFileName();
                    $data[FileInterface::SIZE] = $uploadFileData->getFileSize();
                    $data[FileInterface::EXTENSION] = $uploadFileData->getExtension();
                    $data[FileInterface::MIME_TYPE] = $uploadFileData->getMimeType();
                } else {
                    $data[FileInterface::FILE_PATH] = '';
                }
            }
        } else {
            $data[FileInterface::FILE_PATH] = '';
        }

        return $file->addData($data);
    }

    public function addFileType(array &$file): void
    {
        $tmpFile = [];
        $tmpFile[0]['file'] = $file['file'];
        $tmpFile[0]['tmp_name'] = $tmpFile[0]['name'] = true;
        $file[RegistryConstants::FILE_KEY] = $tmpFile;
        $file[FileInterface::ATTACHMENT_TYPE] = AttachmentType::FILE;
    }

    public function collectInvalidLinks(): array
    {
        return [];
    }
}
