<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductOverrideDataExporter\Model;

use Magento\CatalogDataExporter\Model\Resolver\PriceTableResolver;
use Magento\QueryXml\Model\DB\NameResolver;

class PriceNameResolver extends NameResolver
{
    /**
     * @var PriceTableResolver
     */
    private $priceTableResolver;

    /**
     * @param PriceTableResolver $priceTableResolver
     */
    public function __construct(PriceTableResolver $priceTableResolver)
    {
        $this->priceTableResolver = $priceTableResolver;
    }

    /**
     * Returns element for name
     *
     * @param array $elementConfig
     * @return string
     */
    public function getName($elementConfig)
    {
        return $this->priceTableResolver->getTableName($elementConfig['name']);
    }

    public function getAlias($elementConfig)
    {
        $alias = parent::getName($elementConfig);
        if (isset($elementConfig['alias'])) {
            $alias = $elementConfig['alias'];
        }
        return $alias;
    }
}