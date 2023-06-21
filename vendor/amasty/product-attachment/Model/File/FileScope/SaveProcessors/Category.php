<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\SaveProcessors;

use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\ResourceModel\FileStoreCategory;
use Amasty\ProductAttachment\Model\File\FileType\AddFileType;
use Amasty\ProductAttachment\Model\File\Repository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

class Category implements FileScopeSaveProcessorInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var FileStoreCategory
     */
    private $fileStoreCategory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Repository
     */
    private $fileRepository;

    /**
     * @var AddFileType
     */
    private $addFileType;

    public function __construct(
        FileStoreCategory $fileStoreCategory,
        ManagerInterface $messageManager,
        RequestInterface $request,
        Repository $fileRepository,
        AddFileType $addFileType
    ) {
        $this->messageManager = $messageManager;
        $this->fileStoreCategory = $fileStoreCategory;
        $this->request = $request;
        $this->fileRepository = $fileRepository;
        $this->addFileType = $addFileType;
    }

    /**
     * @param \Amasty\ProductAttachment\Api\Data\FileInterface $params
     *
     * @return array|void
     */
    public function execute($params)
    {
        $storeId = isset($params[RegistryConstants::STORE]) ? (int)$params[RegistryConstants::STORE]
            : (int)$this->request->getParam('store');

        $toDelete = [];
        if (!empty($params[RegistryConstants::TO_DELETE])) {
            $toDelete = $params[RegistryConstants::TO_DELETE];
        }

        if ($files = $params[RegistryConstants::FILES]) {
            $this->saveCategoryRelations($files, $params, $storeId, $toDelete);
        }

        if (!empty($toDelete)) {
            $this->deleteCategoryRelations($toDelete, $params, $storeId);
        }
    }

    public function saveCategoryRelations($files, $params, $storeId, &$toDelete)
    {
        foreach ($files as $file) {
            if ($newFile = $this->addFileType->addType($file, $params)) {
                try {
                    $this->fileRepository->saveAll($newFile, [RegistryConstants::STORE => $storeId]);
                } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
                    $this->messageManager->addErrorMessage(__('Couldn\'t save file'));
                }
            } else {
                unset($toDelete[$file[FileScopeInterface::FILE_ID]]);
                $fileStoreCategory = $this->fileStoreCategory->getCategoryStoreFile(
                    $file[FileScopeInterface::FILE_ID],
                    $params[RegistryConstants::CATEGORY],
                    $storeId
                );
                if (!$fileStoreCategory) {
                    $fileStoreCategory = [];
                }

                foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
                    if (filter_var($file[$field . '_use_defaults'], FILTER_VALIDATE_BOOLEAN)) {
                        $fileStoreCategory[$field] = null;
                    } elseif ($field === 'customer_groups') {
                        $fileStoreCategory[$field] = $file[$field . '_output'];
                    } else {
                        $fileStoreCategory[$field] = $file[$field];
                    }
                }
                $fileStoreCategory[FileScopeInterface::POSITION] = (int)$file[FileScopeInterface::POSITION];
                $fileStoreCategory[FileScopeInterface::CATEGORY_ID] = $params[RegistryConstants::CATEGORY];
                $fileStoreCategory[FileScopeInterface::FILE_ID] = $file[FileScopeInterface::FILE_ID];
                $fileStoreCategory[FileScopeInterface::STORE_ID] = $storeId;
                if ($storeId
                    && $this->fileStoreCategory->isAllStoreViewFile($file[FileScopeInterface::FILE_ID], $storeId)
                ) {
                    $fileCategories = $this->fileStoreCategory->getStoreCategoryIdsByStoreId(
                        $file[FileScopeInterface::FILE_ID],
                        0
                    );
                    unset($fileCategories[$params[RegistryConstants::CATEGORY]]);
                    foreach ($fileCategories as $fileCategory) {
                        $fileCategory[FileScopeInterface::STORE_ID] = $storeId;
                        $fileCategory[FileScopeInterface::FILE_ID] = $file[FileScopeInterface::FILE_ID];
                        $this->fileStoreCategory->insertFileStoreCategoryData($fileCategory);
                    }
                }
                $this->fileStoreCategory->saveFileStoreCategory($fileStoreCategory);
            }
        }
    }

    public function deleteCategoryRelations($toDelete, $params, $storeId)
    {
        foreach (array_keys($toDelete) as $fileId) {
            if (!$storeId) {
                $this->fileStoreCategory->deleteFileByStoreCategory(
                    $fileId,
                    $params[RegistryConstants::CATEGORY],
                    $storeId
                );
            } else {
                $isAllStoreViewFile = $this->fileStoreCategory->isAllStoreViewFile($fileId, $storeId);
                if ($isAllStoreViewFile) {
                    $fileCategories = $this->fileStoreCategory->getStoreCategoryIdsByStoreId(
                        $fileId,
                        0
                    );
                    unset($fileCategories[$params[RegistryConstants::CATEGORY]]);
                    if ($fileCategories) {
                        foreach ($fileCategories as $fileCategory) {
                            $fileCategory[FileScopeInterface::STORE_ID] = $storeId;
                            $fileCategory[FileScopeInterface::FILE_ID] = $fileId;
                            $this->fileStoreCategory->insertFileStoreCategoryData($fileCategory);
                        }
                    } else {
                        $this->fileStoreCategory->insertFileStoreCategoryData([
                            FileScopeInterface::STORE_ID => $storeId,
                            FileScopeInterface::FILE_ID => $fileId,
                            FileScopeInterface::CATEGORY_ID => 0
                        ]);
                    }
                } else {
                    $this->fileStoreCategory->deleteFileByStoreCategory(
                        $fileId,
                        $params[RegistryConstants::CATEGORY],
                        $storeId
                    );
                }
            }
        }
    }
}
