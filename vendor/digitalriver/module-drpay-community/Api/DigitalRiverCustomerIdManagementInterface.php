<?php

namespace Digitalriver\DrPay\Api;

use Magento\Customer\Api\Data\CustomerInterface;

interface DigitalRiverCustomerIdManagementInterface
{
    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getDigitalRiverCustomerId(\Magento\Customer\Api\Data\CustomerInterface $customer): ?string;

    /**
     * @return string|null
     */
    public function getSessionDigitalRiverCustomerId(): ?string;

    /**
     * @param string $digitalRiverCustomerId
     */
    public function persistSessionDigitalRiverCustomerId(string $digitalRiverCustomerId): void;

    /**
     * @param CustomerInterface $customer
     * @return bool
     */
    public function hasDigitalRiverCustomerId(CustomerInterface $customer): bool;
}
