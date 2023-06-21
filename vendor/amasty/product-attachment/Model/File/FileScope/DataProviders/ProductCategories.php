<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\DataProviders;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\ResourceModel\FileStoreCategoryProduct;

class ProductCategories implements FileScopeDataInterface
{
    /**
     * @var Category
     */
    private $categoryDataProvider;

    /**
     * @var FileStoreCategoryProduct
     */
    private $fileStoreCategoryProduct;

    public function __construct(
        Category $categoryDataProvider,
        FileStoreCategoryProduct $fileStoreCategoryProduct
    ) {
        $this->categoryDataProvider = $categoryDataProvider;
        $this->fileStoreCategoryProduct = $fileStoreCategoryProduct;
    }

    /**
     * @inheritdoc
     */
    public function execute($params)
    {
        $productCategories = $params[RegistryConstants::PRODUCT_CATEGORIES];

        if (!empty($productCategories)) {
            return $this->processProductCategories($productCategories, $params);
        }

        return [];
    }

    public function processProductCategories($productCategories, $params)
    {
        $store = $params[RegistryConstants::STORE];

        $result = [];
        foreach ($productCategories as $productCategory) {
            $categoryResult = [];
            $categoryFiles = $this->categoryDataProvider->execute([
                RegistryConstants::CATEGORY => $productCategory,
                RegistryConstants::STORE => $store
            ]);
            if (!empty($categoryFiles)) {
                $categoryResult = $this->processCategoryFiles($productCategory, $categoryFiles, $params);
            }
            if (!empty($categoryResult)) {
                foreach ($categoryResult as $fileId => $file) {
                    $result[$fileId] = $file;
                }
            }
        }

        return array_merge($result);
    }

    public function processCategoryFiles($productCategory, $categoryFiles, $params)
    {
        $categoryResult = [];

        $product = $params[RegistryConstants::PRODUCT];
        $store = $params[RegistryConstants::STORE];
        foreach ($categoryFiles as $categoryFile) {
            if (!isset($result[$categoryFile[FileInterface::FILE_ID]])
                && !in_array(
                    $categoryFile[FileInterface::FILE_ID],
                    $params[RegistryConstants::EXCLUDE_FILES]
                )
            ) {
                $categoryFile[FileScopeInterface::POSITION] += 1000;
                foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
                    $categoryFile[$field . '_use_defaults'] = 1;
                }
                $categoryResult[$categoryFile[FileInterface::FILE_ID]] = $categoryFile;
                $categoryResult[$categoryFile[FileInterface::FILE_ID]][FileScopeInterface::CATEGORY_ID] =
                    $productCategory;
            }
        }
        if (!empty($categoryResult)) {
            $productCategoriesFilesData = $this->fileStoreCategoryProduct->getFilesProductCategoryData(
                array_keys($categoryResult),
                $product,
                $productCategory,
                $store
            );

            if (!empty($productCategoriesFilesData)) {
                foreach ($productCategoriesFilesData as $fileData) {
                    foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
                        if ($fileData[$field] !== null) {
                            $categoryResult[$fileData[FileScopeInterface::FILE_ID]]
                            [$field] = $fileData[$field];
                            $categoryResult[$fileData[FileScopeInterface::FILE_ID]]
                            [$field . '_use_defaults'] = false;
                        }
                    }
                    if ($fileData[FileScopeInterface::POSITION] !== null) {
                        $categoryResult[$fileData[FileScopeInterface::FILE_ID]]
                        [FileScopeInterface::POSITION] = $fileData[FileScopeInterface::POSITION];
                    }
                }
                uasort($categoryResult, function ($file1, $file2) {
                    if ($file1[FileScopeInterface::POSITION] > $file2[FileScopeInterface::POSITION]) {
                        return 1;
                    } elseif ($file1[FileScopeInterface::POSITION] < $file2[FileScopeInterface::POSITION]) {
                        return -1;
                    }

                    return 0;
                });
            }
        }

        return $categoryResult;
    }
}
