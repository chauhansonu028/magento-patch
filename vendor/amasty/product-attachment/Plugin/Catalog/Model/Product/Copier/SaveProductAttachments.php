<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Plugin\Catalog\Model\Product\Copier;

use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProviderInterface;
use Amasty\ProductAttachment\Model\File\FileScope\SaveProcessors\Product as ProductSaveProcessors;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Copier;
use Magento\Framework\App\RequestInterface;

class SaveProductAttachments
{
    /**
     * @var FileScopeDataProviderInterface
     */
    private $fileScopeDataProvider;

    /**
     * @var ProductSaveProcessors
     */
    private $productSaveProcessors;

    public function __construct(
        FileScopeDataProviderInterface $fileScopeDataProvider,
        ProductSaveProcessors $productSaveProcessors,
        RequestInterface $request
    ) {
        $this->fileScopeDataProvider = $fileScopeDataProvider;
        $this->productSaveProcessors = $productSaveProcessors;
    }

    public function afterCopy(Copier $copier, Product $duplicatedProduct, Product $originalProduct): Product
    {
        $productFiles = $this->fileScopeDataProvider->execute(
            [
                RegistryConstants::STORE => $originalProduct->getStoreId(),
                RegistryConstants::PRODUCT => $originalProduct->getId()
            ],
            'product'
        );

        $params[RegistryConstants::PRODUCT] = $duplicatedProduct->getId();
        $toDelete = [];

        $this->productSaveProcessors->saveProductRelations(
            $productFiles,
            $params,
            $duplicatedProduct->getStoreId(),
            $toDelete
        );

        return $duplicatedProduct;
    }
}
