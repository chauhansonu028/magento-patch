<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class FilterableAttributes
{
    /**
     * @var AttributeCollectionFactory
     */
    private AttributeCollectionFactory $attributeCollectionFactory;

    /**
     * @var FilterableAttributesCache
     */
    private FilterableAttributesCache $cache;

    /**
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param FilterableAttributesCache $cache
     */
    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        FilterableAttributesCache $cache
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->cache = $cache;
    }

    /**
     * Returns codes of attributes that have both is_filterable and is_filterable_in_search metadata.
     *
     * @return array
     */
    private function getFilterableAttributeCodes(): array
    {
        $filterableAttributeCodes = $this->cache->load();
        if (empty($filterableAttributeCodes)) {
            $filterableAttributes = $this->attributeCollectionFactory->create();
            $filterableAttributes->addFieldToFilter(
                ['is_filterable', 'is_filterable_in_search'],
                [
                    ['gt' => 0],
                    ['gt' => 0]
                ]
            );
            $filterableAttributeCodes = array_column($filterableAttributes->load()->getData(), 'attribute_code');

            $this->cache->save($filterableAttributeCodes);
        }

        return $filterableAttributeCodes;
    }

    /**
     * Returns mandatory filterable attributes.
     *
     * @return array
     */
    public function getMandatoryFilterableAttributesForLayeredNavigation(): array
    {
        $filterableAttributeCodes = $this->getFilterableAttributeCodes();
        // category is required for layered nav, but not a standard attribute
        return array_merge($filterableAttributeCodes, ['category']);
    }
}
