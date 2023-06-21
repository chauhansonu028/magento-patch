<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\DataProviders\Frontend;

use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\File\FileScope\DataProviders\Product as ProductDataProvider;
use Amasty\ProductAttachment\Model\File\FileScope\DataProviders\ProductCategories;
use Amasty\ProductAttachment\Model\File\Repository;
use Amasty\ProductAttachment\Utils\ProductCategories as ProductCategoriesResolver;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Product implements \Amasty\ProductAttachment\Model\File\FileScope\DataProviders\FileScopeDataInterface
{
    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var File
     */
    private $fileDataProvider;

    /**
     * @var ProductCategories
     */
    private $productCategoriesDataProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCategoriesResolver
     */
    private $productCategoriesResolver;

    /**
     * @var Repository
     */
    private $fileRepository;

    public function __construct(
        ProductDataProvider $productDataProvider,
        ProductCategories $productCategoriesDataProvider,
        ProductCategoriesResolver $productCategoriesResolver,
        StoreManagerInterface $storeManager,
        File $fileDataProvider,
        Repository $fileRepository,
        ConfigProvider $configProvider
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->fileDataProvider = $fileDataProvider;
        $this->productCategoriesDataProvider = $productCategoriesDataProvider;
        $this->configProvider = $configProvider;
        $this->productCategoriesResolver = $productCategoriesResolver;
        $this->storeManager = $storeManager;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute($params)
    {
        $result = [];
        $fileIds = [];
        if ($productFiles = $this->productDataProvider->execute($params)) {
            foreach ($productFiles as $productFile) {
                /** @var \Amasty\ProductAttachment\Model\File\File $file */
                $file = $this->fileRepository->getById($productFile[FileScopeInterface::FILE_ID]);
                $file->addData($productFile);
                $fileIds[] = $file->getFileId();
                if ($file = $this->fileDataProvider->processFileParams($file, $params)) {
                    $result[] = $file;
                }
            }
        }

        if ($this->configProvider->addCategoriesFilesToProducts()) {
            $params[RegistryConstants::EXCLUDE_FILES] = $fileIds;

            if (!empty($categoryIds = $this->getCategoryIds($params))) {
                $params[RegistryConstants::PRODUCT_CATEGORIES] = $categoryIds;
                if ($productCategoriesFiles = $this->productCategoriesDataProvider->execute($params)) {
                    foreach ($productCategoriesFiles as $productCategoryFile) {
                        /** @var \Amasty\ProductAttachment\Model\File\File $file */
                        $file = $this->fileRepository->getById($productCategoryFile[FileScopeInterface::FILE_ID]);
                        $file->addData($productCategoryFile);
                        if ($file = $this->fileDataProvider->processFileParams($file, $params)) {
                            $result[] = $file;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $params - attachment parameters
     *
     * @return array
     */
    private function getCategoryIds(array $params): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        if ($storeId == Store::DEFAULT_STORE_ID) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        return $this->productCategoriesResolver->getCategoryIdsByProduct(
            (int)$params[RegistryConstants::PRODUCT],
            $storeId
        );
    }
}
