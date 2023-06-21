<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Model\Provider\Override;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\QueryXml\Model\QueryProcessor;
use Magento\Framework\DataObject;

/**
 * Data provider for bundle product customer group prices
 */
class BundlePriceOverrides
{
    /**
     * @var string[]
     */
    private $queries = [
        'bundleProductDynamicPriceOverrides'
    ];

    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @var PriceInfoFactory
     */
    private $priceInfoFactory;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param QueryProcessor $queryProcessor
     * @param CollectionFactory $productCollectionFactory
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        QueryProcessor $queryProcessor,
        CollectionFactory $productCollectionFactory,
        PriceInfoFactory $priceInfoFactory
    ) {
        $this->queryProcessor = $queryProcessor;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Format data to reflect the message structure
     *
     * @param array $row
     * @return array
     */
    private function format(array $row, DataObject $product): array
    {
        $priceInfo = $this->priceInfoFactory->create($product);
        $row['prices'] = [
            'minimumPrice' => [
                'finalPrice' => $row['minimumFinalPrice'],
                'regularPrice' => $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getMinimalPrice()->getValue()
            ],
            'maximumPrice' => [
                'finalPrice' => $row['maximumFinalPrice'],
                'regularPrice' => $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getMaximalPrice()->getValue()
            ]
        ];
        return $row;
    }

    /**
     * Get customer group prices
     *
     * @param array $values
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function get(array $values): array
    {
        foreach ($values as $value) {
            $queryArguments['entityIds'][] = $value['productId'];
        }
        $output = [];
        $productIds = [];
        $productsData = [];
        foreach ($this->queries as $queryName) {
            $cursor = $this->queryProcessor->execute($queryName, $queryArguments);
            while ($row = $cursor->fetch()) {
                $productIds[$row['storeId']][$row['productId']] = $row['productId'];
                $uniqueKey = $row['productId'] . $row['websiteCode'] . $row['customerGroupCode'];
                $productsData[$row['storeId']][$uniqueKey] = $row;
            }
            foreach ($productIds as $storeId => $ids) {
                $productCollection = $this->productCollectionFactory->create()->addStoreFilter(
                    $storeId
                )->addFieldToFilter(
                    'entity_id',
                    ['in' => $ids]
                );
                foreach ($productsData[$storeId] as $uniqueKey => $row) {
                    $product = $productCollection->getItemById($row['productId']);
                    if (null !== $product && $product->getData('entity_id')) {
                        $output[$uniqueKey] = $this->format($row, $product);
                    }
                }
            }
        }
        return $output;
    }
}
