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

class StaticInfo implements SecondaryInfoProviderInterface
{
    /**
     * @param array $secondaryInfo
     * @param Quote $quote
     * @param array $apiResult
     * @return array
     */
    public function addSecondaryInfo(array $secondaryInfo, Quote $quote, array $apiResult): array
    {
        $secondaryInfo['checkoutId'] = $apiResult['id'];
        $secondaryInfo['paymentSessionId'] = $apiResult['paymentSessionId'];
        $secondaryInfo['orderTotal'] = $apiResult['orderTotal'];
        return $secondaryInfo;
    }
}
