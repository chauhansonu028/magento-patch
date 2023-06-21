<?php

namespace Digitalriver\DrPay\Api;

use Magento\Quote\Model\Quote;

interface SecondaryInfoProviderInterface
{

    public function addSecondaryInfo(array $secondaryInfo, Quote $quote, array $apiResult): array;
}
