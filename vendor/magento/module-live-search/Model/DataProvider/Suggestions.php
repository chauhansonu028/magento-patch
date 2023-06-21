<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Model\DataProvider;

use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Search\Model\QueryResult;

class Suggestions implements SuggestedQueriesInterface
{
    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isResultsCountEnabled(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * @param QueryInterface $query
     * @return array|QueryResult[]
     */
    public function getItems(QueryInterface $query): array
    {
        return [];
    }
}
