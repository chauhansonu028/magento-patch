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
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 *  Class AggregatePriceProcessor
 */
class SimpleProcessor implements AggregatePriceProcessorInterface
{
    const PROCESSOR_TYPE = 'simple';

    /**
     * @var BundleValidation
     */
    private $bundleValidation;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var DefaultProcessor
     */
    private $defaultProcessor;

    /**
     * @var array
     */
    private $bundleItemOptions = [];

    /**
     * SimpleProcessor constructor.
     * @param BundleValidation $bundleValidation
     * @param JsonSerializer $serializer
     * @param DefaultProcessor $defaultProcessor
     */
    public function __construct(
        BundleValidation $bundleValidation,
        JsonSerializer $serializer,
        DefaultProcessor $defaultProcessor
    ) {
        $this->bundleValidation = $bundleValidation;
        $this->serializer = $serializer;
        $this->defaultProcessor = $defaultProcessor;
    }

    /**
     * Implements logic of calculation simple item based on dr_allocated_percent of option in bundle
     * Or use standart logic if simple is not part of valid bundle
     *
     * @param CartItemInterface $item
     * @return float
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     */
    public function getAggregatePrice(CartItemInterface $item): float
    {
        if ($this->isPartOfValidBundle($item)) {
            $bundleItem = $item->getParentItem();
            $itemTotalPrice = $this->defaultProcessor->getAggregatePrice($bundleItem);
            $options = $this->getBundleOptions($bundleItem);
            foreach ($options as $option) {
                $selections = $option->getSelections();
                if (is_array($selections) && count($selections)) {
                    $selection = $selections[0];
                    if ($selection->getProductId() == $item->getProduct()->getId()) {
                        $drAllocatedPercent = $option->getData('dr_allocated_percent');
                        if (empty($drAllocatedPercent)) {
                            break;
                        }

                        $drAllocatedPercent = (int)$drAllocatedPercent/100;
                        $simpleAggregatePrice = $itemTotalPrice * $drAllocatedPercent / $item->getQty();

                        return round($simpleAggregatePrice, 4);
                    }
                }
            }
        }

        return $this->defaultProcessor->getAggregatePrice($item);
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     */
    private function isPartOfValidBundle(CartItemInterface $item): bool
    {
        $parentItem = $item->getParentItem();
        if (!$parentItem) {
            return false;
        }
        if ($parentItem->getProductType() !== Type::TYPE_BUNDLE) {
            return false;
        }
        $bundleValidationResultMessages = $this->bundleValidation->validate($item->getParentItem()->getProduct());

        return count($bundleValidationResultMessages) === 0;
    }

    /**
     * @param CartItemInterface $item
     * @return array
     */
    private function getBundleOptions(CartItemInterface $item): array
    {
        $itemId = $item->getItemId();
        if (!array_key_exists($itemId, $this->bundleItemOptions)) {
            $bundleProduct = $item->getProduct();
            $this->bundleItemOptions[$itemId] = [];
            /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
            $typeInstance = $bundleProduct->getTypeInstance();
            $bundleOptionsIds = [];
            $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
            if ($optionsQuoteItemOption && $optionsQuoteItemOption->getValue()) {
                $bundleOptionsIds = $this->serializer->unserialize($optionsQuoteItemOption->getValue());
            }
            if ($bundleOptionsIds) {
                /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
                $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $bundleProduct);
                // get and add bundle selections collection
                $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');
                $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());
                if ($bundleSelectionIds) {
                    $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $bundleProduct);
                    $this->bundleItemOptions[$item->getItemId()] = $optionsCollection->appendSelections(
                        $selectionsCollection,
                        true
                    );
                }
            }
        }

        return $this->bundleItemOptions[$item->getItemId()];
    }
}
