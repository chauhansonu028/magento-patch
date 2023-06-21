<?php

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface;
use Digitalriver\DrPay\Helper\Drapi;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;

class DigitalRiverCustomer
{
    /**
     * @var DigitalRiverCustomerIdManagementInterface
     */
    private $customerIdManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Drapi
     */
    private $drApi;

    public function __construct(
        DigitalRiverCustomerIdManagementInterface $customerIdManagement,
        Session $customerSession,
        Drapi $drApi
    ) {
        $this->customerIdManagement = $customerIdManagement;
        $this->customerSession = $customerSession;
        $this->drApi = $drApi;
    }

    /**
     * @param CustomerInterface|null $customer
     * @return string|null
     * @throws LocalizedException
     */
    public function getDrCustomerId($customer = null)
    {
        $drId = empty($customer)
            ? $this->customerIdManagement->getSessionDigitalRiverCustomerId()
            : $this->customerIdManagement->getDigitalRiverCustomerId($customer);

        if (empty($drId) && !empty($customer)) {
            $drId = $this->drApi->createDrId($customer);
        }

        return $drId;
    }
}
