<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Relation\FileType\Processor;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Filesystem\File as FileSystemFile;
use Amasty\ProductAttachment\Model\Filesystem\UploadFileData;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFile;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFileFactory;
use Psr\Log\LoggerInterface;

class TypeProcessor implements TypeProcessorInterface
{
    /**
     * @var FileSystemFile
     */
    public $fileSystemFile;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var ImportFileFactory
     */
    public $importFileFactory;

    public function __construct(
        FileSystemFile $fileSystemFile,
        LoggerInterface $logger,
        ImportFileFactory $importFileFactory
    ) {
        $this->fileSystemFile = $fileSystemFile;
        $this->logger = $logger;
        $this->importFileFactory = $importFileFactory;
    }

    public function processNewFile(array $file, ImportFile $importFile): void
    {
        $filePath = '';
        $fileLink = '';
        if ($this->isNeedToSaveFile()) {
            $uploadFileData = $this->fileSystemFile->getUploadFileData();
            $uploadFileData->setTmpFileName($this->getTmpFileName($file));
            try {
                $this->fileSystemFile->save(
                    $uploadFileData,
                    Directory::IMPORT,
                    true,
                    $importFile->getImportid()
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
            $filePath = $this->getFilePath($uploadFileData);
        } else {
            $fileLink = $this->getFileLink($file);
        }

        $importFile->setFilePath($filePath);
        $importFile->setFileLink($fileLink);
        $importFile->setCustomerGroups($this->getCustomerGroups($file));
    }

    public function processExistFile(ImportFile $file, array $existFile): void
    {
        $file->setFilePath($existFile[FileInterface::FILE_PATH] ?? '');
        $file->setFileLink($existFile[FileInterface::LINK] ?? '');
    }

    protected function isNeedToSaveFile(): bool
    {
        return true;
    }

    protected function getTmpFileName(array $file)
    {
        return $file['file'] ?? '';
    }

    protected function getFilePath(UploadFileData $uploadFileData): string
    {
        return $uploadFileData->getFileName() . '.' . $uploadFileData->getExtension();
    }

    protected function getFileLink(array $file): string
    {
        return $file[FileInterface::LINK] ?? '';
    }

    protected function getCustomerGroups(array $file): string
    {
        if ($file[FileInterface::CUSTOMER_GROUPS] !== ''
            && is_array($file[FileInterface::CUSTOMER_GROUPS])) {
            $customerGroups = implode(',', $file[FileInterface::CUSTOMER_GROUPS]);
        } else {
            $customerGroups = '';
        }

        return $customerGroups;
    }
}
