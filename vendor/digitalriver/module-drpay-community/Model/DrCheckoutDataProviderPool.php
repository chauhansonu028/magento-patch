<?php

namespace Digitalriver\DrPay\Model;

class DrCheckoutDataProviderPool
{
    /**
     * @var array|DrCheckoutDataProviderInterface[]
     */
    private $providers;

    /**
     * DrCheckoutDataProviderPool constructor.
     * @param array $providers
     * @throws \Magento\Framework\Exception\InvalidArgumentException
     */
    public function __construct(array $providers = [])
    {
        foreach ($providers as $provider) {
            if (!($provider instanceof DrCheckoutDataProviderInterface)) {
                throw new \Magento\Framework\Exception\InvalidArgumentException(
                    __('Provided DrCheckoutDataProvider is not the correct type.')
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
        $data = [];

        foreach ($this->providers as $provider) {
            $data = array_merge_recursive($data, $provider->provideCheckoutData($cart));
        }

        return $data;
    }
}
