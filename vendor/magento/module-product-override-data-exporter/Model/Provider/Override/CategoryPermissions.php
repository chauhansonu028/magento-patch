<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Model\Provider\Override;

use Magento\CatalogPermissions\App\ConfigInterface;
use Magento\QueryXml\Model\QueryProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class CategoryPermissions
{
    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param QueryProcessor $queryProcessor
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        QueryProcessor $queryProcessor,
        ScopeConfigInterface $scopeConfig = null
    ) {
        $this->queryProcessor = $queryProcessor;
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @param array $values
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function get(array $values): array
    {
        $categoryPermissionsEnabled = $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        if (!$categoryPermissionsEnabled) {
            return [];
        }
        foreach ($values as $value) {
            $queryArguments['entityIds'][] = $value['productId'];
        }
        $queryArguments['entityIds'] = \array_unique($queryArguments['entityIds']);
        $output = [];
        $cursor = $this->queryProcessor->execute('productCategoryPermissions', $queryArguments);
        while ($row = $cursor->fetch()) {
            $key = $row['productId'] . $row['websiteCode'] . $row['customerGroupCode'];
            $output[$key]['sku'] = $row['sku'];
            $output[$key]['productId'] = $row['productId'];
            $output[$key]['websiteCode'] = $row['websiteCode'];
            $output[$key]['customerGroupCode'] = $row['customerGroupCode'];
            $output[$key]['displayable'] = isset($row['displayable']) && $row['displayable'] == -1;
            $output[$key]['priceDisplayable'] = isset($row['priceDisplayable']) && $row['priceDisplayable'] == -1;
            $output[$key]['addToCartAllowed'] = isset($row['addToCartAllowed']) && $row['addToCartAllowed'] == -1;
        }
        return $output;
    }
}
