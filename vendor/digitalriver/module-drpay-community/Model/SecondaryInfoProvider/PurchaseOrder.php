<?php
/**
 * Collect necessary secondary information from quote and DR api resilt
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\SecondaryInfoProvider;

use Digitalriver\DrPay\Api\SecondaryInfoProviderInterface;
use Magento\Quote\Model\Quote;
use Magento\OfflinePayments\Model\Purchaseorder as PurchaseOrderPaymentModel;

class PurchaseOrder implements SecondaryInfoProviderInterface
{
    public const KEY_PO_AMOUNT = 'purchaseOrderAmount';

    /**
     * @param array $secondaryInfo
     * @param Quote $quote
     * @param array $apiResult
     * @return array
     */
    public function addSecondaryInfo(array $secondaryInfo, Quote $quote, array $apiResult): array
    {
        $secondaryInfo[self::KEY_PO_AMOUNT] = $this->getPurchaseOrderAmount($quote);
        return $secondaryInfo;
    }

    /**
     * @param $quote
     * @return float
     */
    private function getPurchaseOrderAmount(Quote $quote): float
    {
        if ($this->isPurchaseOrderPayment($quote)) {
            //should get grand total which was calculated on checkout, considering all discounts, credits, giftcards
            return (float)$quote->getGrandTotal();
        }

        return 0.0;
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    private function isPurchaseOrderPayment(Quote $quote): bool
    {
        return $quote->getPayment()->getMethod() == PurchaseOrderPaymentModel::PAYMENT_METHOD_PURCHASEORDER_CODE;
    }
}
