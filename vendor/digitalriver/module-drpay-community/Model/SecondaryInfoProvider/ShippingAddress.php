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

class ShippingAddress implements SecondaryInfoProviderInterface
{

    /**
     * @param array $secondaryInfo
     * @param Quote $quote
     * @param array $apiResult
     * @return array
     */
    public function addSecondaryInfo(array $secondaryInfo, Quote $quote, array $apiResult): array
    {
        $secondaryInfo['shippingAddress'] = $this->extractShippingAddress($quote);
        return $secondaryInfo;
    }

    /**
     * @param Quote $quote
     * @return Quote\Address|null
     */
    private function extractShippingAddress(Quote $quote)
    {
        $shippingAddress = null;
        if (!$quote->getIsVirtual() && $quote->getShippingAddress()) {
            $shippingAddress = $quote->getShippingAddress();
            if (empty($shippingAddress->getCity()) &&
                $shippingAddress->getSameAsBilling()
            ) {
                $shippingAddress = $quote->getBillingAddress();
            }
        } elseif ($quote->getIsVirtual() && $quote->getBillingAddress()) {
            $shippingAddress = $quote->getBillingAddress();
        }
        return $shippingAddress;
    }
}
