<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts;

use Amasty\Blog\Api\Data\GetPostRelatedProductsInterface;
use Amasty\Blog\Model\Posts\RelatedProducts\Products\CollectionModifierInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\DB\Select;

class GetPostRelatedProducts implements GetPostRelatedProductsInterface
{
    public const POST_PRODUCT_RELATION_TABLE = 'amasty_blog_posts_products';
    public const POSITION_ALIAS = 'amasty_blog_position';
    public const POST_ID = 'post_id';
    public const PRODUCT_ID = 'product_id';
    public const POSITION = 'position';

    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionModifierInterface[]
     */
    private $collectionModifiers = [];

    /**
     * @var ProductCollection[]
     */
    private $collectionCache = [];

    public function __construct(
        ProductCollectionFactory $collectionFactory,
        array $collectionModifiers = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionModifiers = $collectionModifiers;
    }

    /**
     * @param int $postId
     * @return ProductInterface[]
     */
    public function execute(int $postId): array
    {
        if (!isset($this->collectionCache[$postId])) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect('*');
            $productSelect = $collection->getSelect();
            $productSelect->joinInner(
                ['abpp' => $collection->getTable(self::POST_PRODUCT_RELATION_TABLE)],
                "e.entity_id = abpp.product_id and abpp.post_id = {$postId}",
                [self::POSITION_ALIAS => self::POSITION]
            );
            $productSelect->order(sprintf(
                '%s %s',
                self::POSITION_ALIAS,
                Select::SQL_ASC
            ));

            foreach ($this->collectionModifiers as $modifier) {
                if ($modifier instanceof CollectionModifierInterface) {
                    $modifier->modify($collection);
                }
            }

            $this->collectionCache[$postId] = $collection;
        }

        return $this->collectionCache[$postId]->getItems();
    }
}
