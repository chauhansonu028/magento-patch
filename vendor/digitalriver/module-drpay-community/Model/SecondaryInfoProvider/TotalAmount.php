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

class TotalAmount implements SecondaryInfoProviderInterface
{
    /**
     * @var string[]
     */
    private $extractKeys;

    public function __construct(
        array $extractKeys = []
    ) {
        $this->extractKeys = $extractKeys;
    }

    /**
     * @param array $secondaryInfo
     * @param Quote $quote
     * @param array $apiResult
     * @return array
     */
    public function addSecondaryInfo(array $secondaryInfo, Quote $quote, array $apiResult): array
    {
        $secondaryInfo['amount'] = $this->calculateTotalSecondarySourceAmount($secondaryInfo);
        return $secondaryInfo;
    }

    /**
     * @param $secondaryInfo
     * @return float
     */
    private function calculateTotalSecondarySourceAmount($secondaryInfo): float
    {
        $orderTotal = $secondaryInfo['orderTotal'];
        $result = 0.0;

        foreach ($this->extractKeys as $extractKey) {
            if (array_key_exists($extractKey, $secondaryInfo) && $secondaryInfo[$extractKey] > 0.0) {
                $result += $secondaryInfo[$extractKey];
            }
        }

        if ($result > 0.0 && $orderTotal > 0.0) {
            $result = ($result > $orderTotal) ? $orderTotal : $result;
        }

        return $result;
    }
}
