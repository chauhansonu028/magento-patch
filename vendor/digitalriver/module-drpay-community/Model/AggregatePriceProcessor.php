<?php
/**
 * Calculate Aggregate Item Price
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\AggregatePriceProcessorInterface;
use \Magento\Framework\Exception\InvalidArgumentException;
use Magento\Quote\Api\Data\CartItemInterface;

class AggregatePriceProcessor implements AggregatePriceProcessorInterface
{

    /**
     * @var array
     */
    private $priceProcessors;

    /**
     * AggregatePriceProcessor constructor.
     * @param array $priceProcessors
     * @throws InvalidArgumentException
     */
    public function __construct(
        array $priceProcessors = []
    ) {
        if (!array_key_exists('default', $priceProcessors)) {
            throw new InvalidArgumentException(__('Default price processor is missing'));
        }
        $this->priceProcessors = $priceProcessors;
    }

    /**
     * @param CartItemInterface $item
     * @return float
     */
    public function getAggregatePrice(CartItemInterface $item): float
    {
        $priceProcessor = $this->getProcessorByType($item->getProductType());

        return $priceProcessor->getAggregatePrice($item);
    }

    /**
     * @param $productType
     * @return \Digitalriver\DrPay\Api\AggregatePriceProcessorInterface
     */
    private function getProcessorByType($productType)
    {
        if (array_key_exists($productType, $this->priceProcessors)) {
            return $this->priceProcessors[$productType];
        }

        return $this->priceProcessors['default'];
    }
}
