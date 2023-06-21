<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Model\Provider\Override;

use Magento\QueryXml\Model\QueryProcessor;

/**
 * Data provider for customer group prices
 */
class PriceOverrides
{
    /**
     * @var string[]
     */
    private $queries = [
        'productPriceOverrides',
        'configurableProductPriceOverrides',
        'bundleProductFixedPriceOverrides'
    ];

    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @param QueryProcessor $queryProcessor
     */
    public function __construct(
        QueryProcessor $queryProcessor
    ) {
        $this->queryProcessor = $queryProcessor;
    }

    /**
     * Format data to reflect the message structure
     *
     * @param array $row
     * @return array
     */
    private function format(array $row): array
    {
        $row['prices'] = [
            'minimumPrice' => [
                'finalPrice' => $row['minimumFinalPrice'],
                'regularPrice' => $row['minimumRegularPrice']
            ],
            'maximumPrice' => [
                'finalPrice' => $row['maximumFinalPrice'],
                'regularPrice' => $row['maximumRegularPrice']
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
        foreach ($this->queries as $queryName) {
            $cursor = $this->queryProcessor->execute($queryName, $queryArguments);
            while ($row = $cursor->fetch()) {
                $uniqueKey = $row['productId'] . $row['websiteCode'] . $row['customerGroupCode'];
                $output[$uniqueKey] = $row;
                $output[$uniqueKey] = $this->format($row);
            }
        }
        return $output;
    }
}
