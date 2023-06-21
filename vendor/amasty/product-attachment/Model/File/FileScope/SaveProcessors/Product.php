<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\SaveProcessors;

use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\ResourceModel\FileStoreProduct;
use Amasty\ProductAttachment\Model\File\FileType\AddFileType;
use Amasty\ProductAttachment\Model\File\Repository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

class Product implements FileScopeSaveProcessorInterface
{
    /**
     * @var FileStoreProduct
     */
    private $fileStoreProduct;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

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
        FileStoreProduct $fileStoreProduct,
        ManagerInterface $messageManager,
        RequestInterface $request,
        Repository $fileRepository,
        AddFileType $addFileType
    ) {
        $this->fileStoreProduct = $fileStoreProduct;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->fileRepository = $fileRepository;
        $this->addFileType = $addFileType;
    }

    /**
     * @inheritdoc
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
            $this->saveProductRelations($files, $params, $storeId, $toDelete);
        }

        if (!empty($toDelete)) {
            $this->deleteProductRelations($toDelete, $params, $storeId);
        }
    }

    public function saveProductRelations($files, $params, $storeId, &$toDelete)
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
                $fileStoreProduct = $this->fileStoreProduct->getProductStoreFile(
                    $file[FileScopeInterface::FILE_ID],
                    $params[RegistryConstants::PRODUCT],
                    $storeId
                );
                if (!$fileStoreProduct) {
                    $fileStoreProduct = [];
                }

                foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
                    if (filter_var($file[$field . '_use_defaults'], FILTER_VALIDATE_BOOLEAN)) {
                        $fileStoreProduct[$field] = null;
                    } elseif ($field === 'customer_groups') {
                        $fileStoreProduct[$field] = $file[$field . '_output'] ?? $file[$field];
                    } else {
                        $fileStoreProduct[$field] = $file[$field];
                    }
                }
                $fileStoreProduct[FileScopeInterface::POSITION] = isset($file[FileScopeInterface::POSITION]) ?
                    (int)$file[FileScopeInterface::POSITION]
                    : 0;
                $fileStoreProduct[FileScopeInterface::PRODUCT_ID] = $params[RegistryConstants::PRODUCT];
                $fileStoreProduct[FileScopeInterface::FILE_ID] = $file[FileScopeInterface::FILE_ID];
                $fileStoreProduct[FileScopeInterface::STORE_ID] = $storeId;
                if ($storeId
                    && $this->fileStoreProduct->isAllStoreViewFile($file[FileScopeInterface::FILE_ID], $storeId)
                ) {
                    $fileProducts = $this->fileStoreProduct->getStoreProductIdsByStoreId(
                        $file[FileScopeInterface::FILE_ID],
                        0
                    );
                    unset($fileProducts[$params[RegistryConstants::PRODUCT]]);
                    foreach ($fileProducts as $fileProduct) {
                        $fileProduct[FileScopeInterface::STORE_ID] = $storeId;
                        $fileProduct[FileScopeInterface::FILE_ID] = $file[FileScopeInterface::FILE_ID];
                        $this->fileStoreProduct->insertFileStoreProductData($fileProduct);
                    }
                }
                $this->fileStoreProduct->saveFileStoreProduct($fileStoreProduct);
            }
        }
    }

    public function deleteProductRelations($toDelete, $params, $storeId)
    {
        foreach (array_keys($toDelete) as $fileId) {
            if (!$storeId) {
                $this->fileStoreProduct->deleteFileByStoreProduct(
                    $fileId,
                    $params[RegistryConstants::PRODUCT],
                    $storeId
                );
            } else {
                $isAllStoreViewFile = $this->fileStoreProduct->isAllStoreViewFile($fileId, $storeId);
                if ($isAllStoreViewFile) {
                    $fileProducts = $this->fileStoreProduct->getStoreProductIdsByStoreId(
                        $fileId,
                        0
                    );
                    unset($fileProducts[$params[RegistryConstants::PRODUCT]]);
                    if ($fileProducts) {
                        foreach ($fileProducts as $fileProduct) {
                            $fileProduct[FileScopeInterface::STORE_ID] = $storeId;
                            $fileProduct[FileScopeInterface::FILE_ID] = $fileId;
                            $this->fileStoreProduct->insertFileStoreProductData($fileProduct);
                        }
                    } else {
                        $this->fileStoreProduct->insertFileStoreProductData([
                            FileScopeInterface::STORE_ID => $storeId,
                            FileScopeInterface::FILE_ID => $fileId,
                            FileScopeInterface::PRODUCT_ID => 0
                        ]);
                    }
                } else {
                    $this->fileStoreProduct->deleteFileByStoreProduct(
                        $fileId,
                        $params[RegistryConstants::PRODUCT],
                        $storeId
                    );
                }
            }
        }
    }
}
