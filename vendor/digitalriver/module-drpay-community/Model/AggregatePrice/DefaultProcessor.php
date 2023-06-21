<?php
/**
 * Calculate Aggregate Bundle Price
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\AggregatePrice;

use Digitalriver\DrPay\Api\AggregatePriceProcessorInterface;
use Digitalriver\DrPay\Helper\Config;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 *  Class AggregatePriceProcessor
 */
class DefaultProcessor implements AggregatePriceProcessorInterface
{
    const PROCESSOR_TYPE = 'default';

    /**
     * @var Config
     */
    private $config;

    /**
     * DefaultProcessor constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param CartItemInterface $item
     * @return float
     */
    public function getAggregatePrice(CartItemInterface $item): float
    {
        $taxInclusive = $this->config->isTaxInclusive();
        $price = $item->getCalculationPrice();
        if ($taxInclusive) {
            $price = $item->getCalculationPriceOriginal() + $item->getTaxAmount();
        }

        return round((float)$price, 4);
    }
}
