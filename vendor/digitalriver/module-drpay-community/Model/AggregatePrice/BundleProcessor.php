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
use Digitalriver\DrPay\Model\BundleValidation;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 *  Class AggregatePriceProcessor
 */
class BundleProcessor implements AggregatePriceProcessorInterface
{

    const PROCESSOR_TYPE = 'bundle';

    /**
     * @var BundleValidation
     */
    private $bundleValidation;

    /**
     * @var SimpleProcessor
     */
    private $simpleProcessor;

    /**
     * @var DefaultProcessor
     */
    private $defaultProcessor;

    /**
     * BundleProcessor constructor.
     * @param BundleValidation $bundleValidation
     * @param SimpleProcessor $simpleProcessor
     * @param DefaultProcessor $defaultProcessor
     */
    public function __construct(
        BundleValidation $bundleValidation,
        SimpleProcessor $simpleProcessor,
        DefaultProcessor $defaultProcessor
    ) {
        $this->bundleValidation = $bundleValidation;
        $this->simpleProcessor = $simpleProcessor;
        $this->defaultProcessor = $defaultProcessor;
    }

    /**
     * @param CartItemInterface $item
     * @return float
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     */
    public function getAggregatePrice(CartItemInterface $item): float
    {
        $bundleValidationResultMessages = $this->bundleValidation->validate($item->getProduct());
        if (count($bundleValidationResultMessages) === 0) {
            $sumByItems = 0;
            foreach ($item->getChildren() as $childItem) {
                $sumByItems += $this->simpleProcessor->getAggregatePrice($childItem) * $item->getQty();
            }
            return $sumByItems;
        }

        return $this->defaultProcessor->getAggregatePrice($item);
    }
}
