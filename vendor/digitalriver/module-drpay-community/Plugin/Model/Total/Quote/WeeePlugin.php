<?php
/**
 * Create DR Allocated Percent field
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Plugin\Model\Total\Quote;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Helper\Data;
use Digitalriver\DrPay\Helper\Drapi;
use Digitalriver\DrPay\Model\DrCheckoutManagement;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Weee\Helper\Data as WeeHelper;
use Magento\Weee\Model\Total\Quote\Weee;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Add regulatory fees to quote
 */
class WeeePlugin
{
    /**
     * @var WeeHelper
     */
    private $weeeData;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var array
     */
    private $fees = [];

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /** @var DrCheckoutManagement  */
    private $drCheckoutManagement;

    public function __construct(
        WeeHelper $weeeData,
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        Store $store,
        Config $config,
        SerializerInterface $serialize,
        DrCheckoutManagement $drCheckoutManagement
    ) {
        $this->weeeData = $weeeData;
        $this->checkoutSession = $checkoutSession;
        $this->priceCurrency = $priceCurrency;
        $this->store = $store;
        $this->config = $config;
        $this->serialize = $serialize;
        $this->drCheckoutManagement = $drCheckoutManagement;
    }

    /**
     * @param Weee $subject
     * @param $result
     * @param $quote
     * @param $shippingAssignment
     * @param $total
     * @return array
     */
    public function afterCollect(Weee $subject, $result, $quote, $shippingAssignment, $total)
    {
        $items = $shippingAssignment->getItems();
        if (empty($items) || !$this->config->getIsEnabled()) {
            return $result;
        }

        $this->drCheckoutManagement->setCheckoutByQuote($quote);

        $weeeCodeToItemMap = [];
        $weeeTotalExclTax = 0;

        $drFees = $this->getDrFees();
        foreach ($items as $item) {

            $skuFeeData = $drFees[$item->getId()] ?? [];

            $fees = [];
            $totalFeeAmount = 0;
            $totalFeeAmountRows = 0;
            $associatedTaxables = $item->getAssociatedTaxables() ?: [];

            foreach ($skuFeeData as $skuFee) {
                $fees[] = [
                    'title' => $skuFee['value'],
                    'base_amount' => $skuFee['perUnitAmount'],
                    'amount' => $skuFee['perUnitAmount'],
                    'row_amount' => $skuFee['amount'],
                    'base_row_amount' => $skuFee['amount'],
                    'base_amount_incl_tax' => $skuFee['perUnitAmount'],
                    'amount_incl_tax' => $skuFee['perUnitAmount'],
                    'row_amount_incl_tax' => $skuFee['amount'],
                    'base_row_amount_incl_tax' => $skuFee['amount'],
                    'code' => "dr_fee_{$skuFee['id']}"
                ];

                $totalFeeAmount += $this->priceCurrency->round($skuFee['perUnitAmount']);
                $totalFeeAmountRows += $this->priceCurrency->round($skuFee['amount']);

                $weeeItemCode = Weee::ITEM_CODE_WEEE_PREFIX . '-' . $skuFee['value'];

                $associatedTaxable = [
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => Weee::ITEM_TYPE,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => $weeeItemCode,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $skuFee['perUnitAmount'],
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $skuFee['perUnitAmount'],
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => $item->getTotalQty(),
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $item->getProduct()->getTaxClassId(),
                ];
                $associatedTaxables = $this->addTaxable($associatedTaxable, $associatedTaxables);
                $weeeCodeToItemMap[$weeeItemCode] = $item;
            }

            $baseTotalFeeAmount = $this->config->convertToBaseCurrency($totalFeeAmount);
            $baseTotalFeeAmountRows = $this->config->convertToBaseCurrency($totalFeeAmountRows);

            $item->setAssociatedTaxables($associatedTaxables)
                ->setWeeeTaxAppliedAmount($totalFeeAmount)
                ->setBaseWeeeTaxAppliedAmount($baseTotalFeeAmount)
                ->setWeeeTaxAppliedRowAmount($totalFeeAmountRows)
                ->setBaseWeeeTaxAppliedRowAmnt($baseTotalFeeAmountRows);

            $item->setWeeeTaxAppliedAmountInclTax($totalFeeAmount)
                ->setBaseWeeeTaxAppliedAmountInclTax($baseTotalFeeAmount)
                ->setWeeeTaxAppliedRowAmountInclTax($totalFeeAmountRows)
                ->setBaseWeeeTaxAppliedRowAmntInclTax($baseTotalFeeAmountRows);

            $this->weeeData->setApplied($item, $fees);
            $weeeTotalExclTax += $this->priceCurrency->round($totalFeeAmountRows);
        }

        $baseWeeeTotalExclTax = $this->config->convertToBaseCurrency($weeeTotalExclTax);

        if (!empty($weeeCodeToItemMap)) {
            $total->setWeeeTotalExclTax($weeeTotalExclTax);
            $total->setWeeeBaseTotalExclTax($baseWeeeTotalExclTax);
            $total->setWeeeCodeToItemMap($weeeCodeToItemMap);
        }

        $total->setSubtotalInclTax(
            $total->getSubtotalInclTax() + $weeeTotalExclTax
        );

        $total->setBaseSubtotalInclTax(
            $total->getBaseSubtotalInclTax() + $baseWeeeTotalExclTax
        );

        return $result;
    }

    /**
     * @return array
     */
    private function getDrFees(): array
    {
        if (!empty($this->fees)) {
            return $this->fees;
        }

        $checkoutId = $this->checkoutSession->getDrCheckoutId();
        if (empty($checkoutId)) {
            return [];
        }

        $checkoutData = $this->serialize->unserialize($this->checkoutSession->getDrResult());
        if (empty($checkoutData) || empty($checkoutData['items'])) {
            return [];
        }

        $items = [];
        foreach ($checkoutData['items'] as $item) {
            if (empty($item['fees']['details'])) {
                continue;
            }

            // need to use this id because skus can be ambiguous with bundles and singles
            if (isset($item['metadata']['magento_quote_item_id'])) {
                $magentoQuoteItemId = $item['metadata']['magento_quote_item_id'];
                foreach ($item['fees']['details'] as $feeItem) {
                    if (!isset($items[$magentoQuoteItemId])) {
                        $items[$magentoQuoteItemId] = [$this->feeRowData($feeItem)];
                        continue;
                    }
                    $items[$magentoQuoteItemId][] = $this->feeRowData($feeItem);
                }
            }
        }

        $this->fees = $items;
        return $this->fees;
    }

    /**
     * @param $feeDetails
     * @return array
     */
    private function feeRowData($feeDetails): array
    {
        $titleData = explode('_', $feeDetails['type']);
        $title = array_map('ucfirst', $titleData);

        return [
            'value' => implode(' ', $title),
            'code' => $feeDetails['id'],
            'amount' => $feeDetails['amount'],
            'perUnitAmount' => $feeDetails['perUnitAmount'],
            'id' => $feeDetails['id']
        ];
    }

    /**
     * @param array $taxableItem
     * @param array $taxables
     * @return array
     */
    private function addTaxable(array $taxableItem, array $taxables): array
    {
        $weeeItemCode = $taxableItem[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE];
        foreach ($taxables as $index => $taxable) {
            if ($taxable[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE] === $weeeItemCode) {
                $taxables[$index] = $taxableItem;
                return $taxables;
            }
        }

        $taxables[] = $taxableItem;
        return $taxables;
    }
}
