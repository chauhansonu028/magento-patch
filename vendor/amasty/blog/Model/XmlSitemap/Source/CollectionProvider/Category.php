<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\XmlSitemap\Source\CollectionProvider;

use Amasty\Blog\Model\ResourceModel\Categories\CollectionFactory;
use Amasty\Blog\Model\Source\CategoryStatus;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Category implements SitemapCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection(int $storeId): AbstractCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addStatusFilter(CategoryStatus::STATUS_ENABLED);
        $collection->addStoreWithDefault((int)$storeId);
        $collection->setSortOrder(Select::SQL_ASC);

        return $collection;
    }
}
