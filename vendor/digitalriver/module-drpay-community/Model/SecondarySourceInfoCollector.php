<?php

namespace Digitalriver\DrPay\Model;

use Magento\Quote\Model\Quote;

/**
 * SecondarySourceInfoCollector class - used to collect necessary data for secondary source to be sent to API
 */
class SecondarySourceInfoCollector
{

    /**
     * @var \Digitalriver\DrPay\Api\SecondaryInfoProviderInterface[]
     */
    private $secondarySourceInfoCollectors;

    public function __construct(array $secondarySourceInfoCollectors = [])
    {
        ksort($secondarySourceInfoCollectors);
        $this->secondarySourceInfoCollectors = $secondarySourceInfoCollectors;
    }

    /**
     * @param array $data
     * @param array $apiResult
     * @param Quote $quote
     * @return array
     */
    public function collect(array $apiResult, Quote $quote): array
    {
        $secondaryInfo = [];
        foreach ($this->secondarySourceInfoCollectors as $collector) {
            $secondaryInfo = $collector->addSecondaryInfo($secondaryInfo, $quote, $apiResult);
        }

        return $secondaryInfo;
    }
}
