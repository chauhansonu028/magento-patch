<?php

namespace Digitalriver\DrPay\Model\Customer;

class CustomerIdCreator
{
    /**
     * Get Digital River formed Customer ID.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return string
     */
    public function getCustomerId(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return sha1($customer->getEmail() . $customer->getId());
    }
}
