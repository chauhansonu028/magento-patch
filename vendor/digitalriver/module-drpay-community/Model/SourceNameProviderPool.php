<?php

namespace Digitalriver\DrPay\Model;

class SourceNameProviderPool
{
    /**
     * @var SourceNameProviderInterface[] $providers
     */
    private $providers;

    public function __construct(
        array $providers = []
    ) {
        foreach ($providers as $provider) {
            if (!($provider instanceof SourceNameProviderInterface)) {
                throw new \Magento\Framework\Exception\InvalidArgumentException(
                    __('Provided SourceNameProvider is not the correct type.')
                );
            }
        }

        $this->providers = $providers;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function execute(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $sourceNames = [];

        foreach ($this->providers as $provider) {
            $sourceNames = array_merge_recursive($provider->getSourceNameFromQuote($cart));
        }

        return $sourceNames;
    }
}
