<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\FileRepositoryInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\SaveFileScopeInterface;
use Amasty\ProductAttachment\Model\File\FileType\TypeProcessorProvider;
use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Filesystem\File as FileSystemFile;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;

class Repository implements FileRepositoryInterface
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ResourceModel\File
     */
    private $fileResource;

    /**
     * @var FileInterface[]
     */
    private $files;

    /**
     * @var SaveFileScopeInterface
     */
    private $saveFileStore;

    /**
     * @var FileSystemFile
     */
    private $file;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    public function __construct(
        FileFactory $fileFactory,
        ResourceModel\File $fileResource,
        SaveFileScopeInterface $saveFileStore,
        Random $random,
        FileSystemFile $file,
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->fileFactory = $fileFactory;
        $this->fileResource = $fileResource;
        $this->saveFileStore = $saveFileStore;
        $this->file = $file;
        $this->random = $random;
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    /**
     * Save file.
     *
     * @param \Amasty\ProductAttachment\Api\Data\FileInterface $file
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function save(\Amasty\ProductAttachment\Api\Data\FileInterface $file)
    {
        try {
            if ($file->getFileId()) {
                $file = $this->getById($file->getFileId())->addData($file->getData());
            }

            $this->fileResource->save($file);
            unset($this->files[$file->getFileId()]);
        } catch (\Exception $e) {
            if ($file->getFileId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save file with ID %1. Error: %2',
                        [$file->getFileId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new file. Error: %1', $e->getMessage()));
        }

        return $file;
    }

    /**
     * @inheritdoc
     */
    public function saveAll(
        FileInterface $file,
        $params = [],
        $checkExtension = true
    ) {
        try {
            if ($this->needToUpdate($file)) {
                $typeProcessor = $this->typeProcessorProvider->getProcessorByType($file->getAttachmentType());
                $typeProcessor->updateFile($file, $checkExtension);
            }

            if (!$file->getFilePath()) {
                $file->unsetData(FileInterface::FILE_PATH);
            }

            if ($file->getFileId()) {
                $file = $this->getById($file->getFileId())->addData($file->getData());
            }

            if (empty($file->getUrlHash())) {
                $file->setUrlHash($this->random->getUniqueHash());
            }

            $this->fileResource->save($file);
            $this->saveFileStore->execute(array_merge($params, [RegistryConstants::FILE => $file]), 'file');
            unset($this->files[$file->getFileId()]);
        } catch (\Exception $e) {
            if ($file->getFileId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save file with ID %1. Error: %2',
                        [$file->getFileId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new file. Error: %1', $e->getMessage()));
        }

        return $file;
    }

    /**
     * @param FileInterface $file
     *
     * @return bool
     *
     * @throws NoSuchEntityException
     */
    public function needToUpdate($file)
    {
        if ($file->getAttachmentType() !== AttachmentType::LINK) {
            return true;
        }

        if (!$file->getFileId()) {
            return true;
        }

        if (($origFile = $this->getById($file->getFileId())) && ($origFile->getLink() != $file->getLink())) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getById($fileId)
    {
        if (!isset($this->files[$fileId])) {
            /** @var \Amasty\ProductAttachment\Model\File\File $file */
            $file = $this->fileFactory->create();
            $this->fileResource->load($file, $fileId);
            if (!$file->getFileId()) {
                throw new NoSuchEntityException(__('File with specified ID "%1" not found.', $fileId));
            }
            $this->files[$fileId] = $file;
        }

        return $this->files[$fileId];
    }

    /**
     * @inheritdoc
     */
    public function getByHash($hash)
    {
        /** @var \Amasty\ProductAttachment\Model\File\File $file */
        $file = $this->fileFactory->create();
        $this->fileResource->load($file, $hash, FileInterface::URL_HASH);
        if (!$file->getFileId()) {
            throw new NoSuchEntityException(__('File with specified Hash "%1" not found.', $hash));
        }
        $this->files[$file->getFileId()] = $file;

        return $file;
    }

    /**
     * Delete file.
     *
     * @param \Magento\Framework\Model\AbstractModel|\Amasty\ProductAttachment\Api\Data\FileInterface $file
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Amasty\ProductAttachment\Api\Data\FileInterface $file)
    {
        try {
            $this->file->deleteFile($file->getFilePath(), Directory::ATTACHMENT);
            $this->fileResource->delete($file);
            unset($this->files[$file->getFileId()]);
        } catch (\Exception $e) {
            if ($file->getFileId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove file with ID %1. Error: %2',
                        [$file->getFileId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove icon. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * Delete file by ID.
     *
     * @param int $fileId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($fileId)
    {
        if (!($file = $this->getById($fileId))) {
            throw new NoSuchEntityException(__('File with specified ID "%1" not found.', $fileId));
        } else {
            $this->delete($file);

            return true;
        }
    }
}
