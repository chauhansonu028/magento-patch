<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts;

use Amasty\Blog\Api\Data\GetPostRelatedProductsInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\Posts\RelatedProducts\Products\CollectionModifierInterface;
use Amasty\Blog\Model\ResourceModel\Posts\Save\SavePostProductRelations;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zend_Db_Expr;

class GetPostRelatedProductsForPreview implements GetPostRelatedProductsInterface
{
    const IS_PREVIEW_FROM_SAVED_FLAG = 'amblog_related_posts_preview_saved';

    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $collectionModifiers;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetPostRelatedProducts
     */
    private $getPostRelatedProducts;

    public function __construct(
        ProductCollectionFactory $collectionFactory,
        Registry $registry,
        GetPostRelatedProducts $getPostRelatedProducts,
        array $collectionModifiers = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionModifiers = $collectionModifiers;
        $this->registry = $registry;
        $this->getPostRelatedProducts = $getPostRelatedProducts;
    }

    public function execute(int $postId): array
    {
        return $this->getCurrentPost()->getData(self::IS_PREVIEW_FROM_SAVED_FLAG)
           ? $this->getPostRelatedProducts->execute($postId)
           : $this->getProductsForRegistryProduct()->getItems();
    }

    /**
     * @return int[]
     */
    private function getProductsIdsForPreview(): array
    {
        $post = $this->getCurrentPost();
        $productsData = $post->getData(SavePostProductRelations::DATA_SECTION) ?: [];
        usort($productsData, function (array $productPositionA, array $productPositionB): int {
            $positionA = (int) $productPositionA[GetPostRelatedProducts::POSITION_ALIAS];
            $positionB = (int) $productPositionB[GetPostRelatedProducts::POSITION_ALIAS];

            return (int) $positionA <=> $positionB;
        });
        $productsData = array_map(function (array $productPosition): int {
            return (int) $productPosition['entity_id'];
        }, $productsData);

        return $productsData;
    }

    private function getCurrentPost(): ?PostInterface
    {
        return $this->registry->registry(Registry::CURRENT_POST);
    }

    private function getProductsForRegistryProduct(): ProductCollection
    {
        $collection = $this->collectionFactory->create();
        $productsIds = $this->getProductsIdsForPreview();
        $collection->addIdFilter($productsIds);
        $orderExpression = new Zend_Db_Expr(sprintf('FIELD(e.entity_id, %s)', join(',', $productsIds)));
        $collection->getSelect()->order($orderExpression);

        foreach ($this->collectionModifiers as $modifier) {
            if ($modifier instanceof CollectionModifierInterface) {
                $modifier->modify($collection);
            }
        }

        return $collection;
    }
}
