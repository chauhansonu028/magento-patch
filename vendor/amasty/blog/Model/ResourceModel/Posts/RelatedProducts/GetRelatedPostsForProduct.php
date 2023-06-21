<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts;

use Amasty\Blog\Api\Data\GetRelatedPostsForProductInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory as PostsCollectionFactory;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class GetRelatedPostsForProduct implements GetRelatedPostsForProductInterface
{
    /**
     * @var PostsCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PostInterface[][]
     */
    private $postsByProductId = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PostsCollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $productId
     * @return PostInterface[]
     */
    public function execute(int $productId): array
    {
        if (!isset($this->postsByProductId[$productId])) {
            $collection = $this->collectionFactory->create();
            $relationTableAlias = 'abpp';
            $collection->join(
                [$relationTableAlias => GetPostRelatedProducts::POST_PRODUCT_RELATION_TABLE],
                sprintf(
                    '%1$s.%2$s = %3$s.%4$s and %3$s.%5$s = %6$d',
                    'main_table',
                    $collection->getIdFieldName(),
                    $relationTableAlias,
                    GetPostRelatedProducts::POST_ID,
                    GetPostRelatedProducts::PRODUCT_ID,
                    $productId
                ),
                []
            );
            $collection->addStoreWithDefault((int)$this->storeManager->getStore()->getId());
            $collection->setUrlKeyIsNotNull();
            $collection->addStoreFilter($this->storeManager->getStore()->getId());
            $collection->addFieldToFilter(PostInterface::STATUS, ['eq' => PostStatus::STATUS_ENABLED]);
            $collection->addOrder(PostInterface::PUBLISHED_AT, Select::SQL_DESC);
            $this->postsByProductId[$productId] = $collection;
        }

        return $this->postsByProductId[$productId]->getItems();
    }
}
