<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import;

use Amasty\Base\Model\Serializer;
use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Filesystem\File;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFile as ImportFileModel;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFileFactory;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\TypeProcessorProvider;
use Amasty\ProductAttachment\Model\Import\ResourceModel\Import;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;

class Repository
{
    /**
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * @var ResourceModel\Import
     */
    private $importResource;

    /**
     * @var array
     */
    private $imports = [];

    /**
     * @var \Amasty\ProductAttachment\Model\Filesystem\File
     */
    private $moveFile;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var array
     */
    private $importedFiles = [];

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TypeProcessorProvider[]
     */
    private $typeProcessorProvider;

    /**
     * @var ImportFileFactory
     */
    private $importFileFactory;

    public function __construct(
        ImportFactory $importFactory,
        File $moveFile,
        Import $importResource,
        Filesystem $filesystem,
        Serializer $serializer,
        TypeProcessorProvider $typeProcessorProvider,
        ImportFileFactory $importFileFactory
    ) {
        $this->importFactory = $importFactory;
        $this->importResource = $importResource;
        $this->moveFile = $moveFile;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->serializer = $serializer;
        $this->typeProcessorProvider = $typeProcessorProvider;
        $this->importFileFactory = $importFileFactory;
    }

    /**
     * @param $importId
     *
     * @throws NoSuchEntityException
     * @return \Amasty\ProductAttachment\Model\Import\Import
     */
    public function getById($importId)
    {
        if (!isset($this->imports[$importId])) {
            /** @var \Amasty\ProductAttachment\Model\Import\Import $import*/
            $import = $this->importFactory->create();
            $this->importResource->load($import, $importId);
            if (!$import->getImportId()) {
                throw new NoSuchEntityException(__('Import with specified ID "%1" not found.', $importId));
            }
            $this->imports[$importId] = $import;
        }

        return $this->imports[$importId];
    }

    public function getImportFilesByImportId(int $importId): string
    {
        return $this->getById($importId)->getImportFile();
    }

    public function save(\Amasty\ProductAttachment\Model\Import\Import $import)
    {
        try {
            if ($import->getImportId()) {
                $import = $this->getById($import->getImportId())->addData($import->getData());
            }
            $import->setStoreIds($import->getStoreIds());
            $this->importResource->save($import);
            unset($this->imports[$import->getIconId()]);
        } catch (\Exception $e) {
            if ($import->getImportId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save import with ID %1. Error: %2',
                        [$import->getImportId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new import. Error: %1', $e->getMessage()));
        }

        return $import;
    }

    /**
     * @param int $importId
     * @param array $files
     *
     * @return array
     */
    public function getImportFilesData(int $importId, array $files): array
    {
        $data = [];
        $fileId = 1;
        foreach ($files as $file) {
            $importFile = $this->importFileFactory->create();
            $importFile->setImportId($importId);
            // Process new files.
            $importFile->setIsFileNew(false);
            $this->processImportFileData($file, $importFile);
            // Process exist import files.
            if (false === $importFile->isFileNew() && isset($file['file_id'])) {
                $this->processExistFile($file, $importFile);
            }
            if (!$importFile->getFileId()) {
                $importFile->setFileId($fileId);
                $importFile->setShowFileId($fileId);
                $importFile->setImportId($importId);
                $importFile->setExtension($file[FileInterface::EXTENSION]);
                $importFile->setLabel($file[FileInterface::LABEL]);
                $importFile->setFilename($file[FileInterface::FILENAME]);
                $importFile->setIncludeInOrder($file[FileInterface::INCLUDE_IN_ORDER]);
                $importFile->setIsVisible($file[FileInterface::IS_VISIBLE]);
            }
            $data[$importFile->getFileId()] = $importFile;
            ++$fileId;
        }

        return $data;
    }

    // Process newly loaded files.
    public function processImportFileData(array $file, ImportFileModel $importFile): void
    {
        foreach ($this->typeProcessorProvider->getProcessors() as $type => $processor) {
            if (!empty($file[$type])) {
                $processor['processor']->processNewFile($file, $importFile);
                $importFile->setFileType($processor['typeCode']);
                $importFile->setIsFileNew(true);
                break;
            }
        }
    }

    private function processExistFile(array $file, ImportFileModel $importFile): void
    {
        if (empty($this->importedFiles)) {
            $this->initImportFiles($importFile->getImportId());
        }
        if (isset($this->importedFiles[$file['file_id']])) {
            foreach ($this->typeProcessorProvider->getProcessors() as $type => $processor) {
                if (!empty($this->importedFiles[$file['file_id']][$type])) {
                    $processor['processor']->processExistFile($importFile, $this->importedFiles[$file['file_id']]);
                    break;
                }
            }

            $importFile->setCustomerGroups($file[FileInterface::CUSTOMER_GROUPS . '_output']);
            $importFile->setIsFileNew(false);
            $importFile->setFileType($this->importedFiles[$file['file_id']][FileInterface::ATTACHMENT_TYPE]);
        }
    }

    private function initImportFiles(int $importId): void
    {
        $import = $this->getById($importId);
        $importedFiles = $this->serializer->unserialize($import->getImportFile()) ?? [];
        foreach ($importedFiles as $file) {
            if (isset($file['file_id'])) {
                $this->importedFiles[$file['file_id']] = $file;
            }
        }
    }

    /**
     * @param int $importId
     */
    public function deleteById($importId)
    {
        try {
            $import = $this->getById((int)$importId);
            $this->mediaDirectory->delete(
                Directory::DIRECTORY_CODES[Directory::IMPORT] . DIRECTORY_SEPARATOR . (int)$importId
            );
            $this->importResource->delete($import);
        } catch (\Exception $e) {
            null;
        }
    }
}
