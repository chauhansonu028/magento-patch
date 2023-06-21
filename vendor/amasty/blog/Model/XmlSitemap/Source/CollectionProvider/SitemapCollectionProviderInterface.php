<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\XmlSitemap\Source\CollectionProvider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

interface SitemapCollectionProviderInterface
{
    /**
     * @return AbstractCollection
     */
    public function getCollection(int $storeId): AbstractCollection;
}
