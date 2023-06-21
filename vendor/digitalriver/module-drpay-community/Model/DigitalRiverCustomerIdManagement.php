<?php

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Setup\Patch\Data\AddDrCustomerId;
use Magento\Customer\Api\Data\CustomerInterface;
use Digitalriver\DrPay\Logger\Logger;

class DigitalRiverCustomerIdManagement implements \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager; 
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return string|null
     */
    public function getDigitalRiverCustomerId(\Magento\Customer\Api\Data\CustomerInterface $customer): ?string
    {
        $customerId = null;
        $currentStoreId = $this->storeManager->getStore()->getId();
        $storeId = $this->customerSession->getCurrentCustomerStoreId();
        if($currentStoreId == $storeId){
            if ($customer instanceof \Magento\Framework\Api\CustomAttributesDataInterface) {
                $attribute = $customer->getCustomAttribute(
                    \Digitalriver\DrPay\Setup\Patch\Data\AddDrCustomerId::ATTRIBUTE_CODE
                );
                
                    if ($attribute) {
                        $customerId = (string)$attribute->getValue();
                    }
            }
        }
        return $customerId;
    }

    /**
     * If customer is logged in, use attribute value. Otherwise, use session value.
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSessionDigitalRiverCustomerId(): ?string
    {
        $customerId = null;
        $currentStoreId = $this->storeManager->getStore()->getId();
        if ($this->customerSession->isLoggedIn()) {
            $storeId = $this->customerSession->getCurrentCustomerStoreId();
            if($storeId === $currentStoreId){
                $customerId = $this->getDigitalRiverCustomerId($this->customerSession->getCustomerData());
            }     
        } else {
            $customerId = $this->checkoutSession->getData('dr_customer_id');
        }
        return $customerId;
    }

    /**
     * @param string $digitalRiverCustomerId
     */
    public function persistSessionDigitalRiverCustomerId(string $digitalRiverCustomerId): void
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomerData();

            if ($customer instanceof \Magento\Framework\Api\CustomAttributesDataInterface) {
                $attribute = $customer->getCustomAttribute(
                    \Digitalriver\DrPay\Setup\Patch\Data\AddDrCustomerId::ATTRIBUTE_CODE
                );

                if (($attribute != null && $attribute->getValue() != $digitalRiverCustomerId) || $attribute == null) {
                    $customer->setCustomAttribute(
                        \Digitalriver\DrPay\Setup\Patch\Data\AddDrCustomerId::ATTRIBUTE_CODE,
                        $digitalRiverCustomerId
                    );

                    try {
                        $this->customerRepository->save($customer);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->logger->error(
                            __('Digital River: Failed to persist DR customer ID.'),
                            [
                                'exception' => $e->getMessage(),
                                'customerId' => $customer->getId(),
                                'drId' => $digitalRiverCustomerId
                            ]
                        );
                    }
                }
            }
        } else {
            $this->checkoutSession->setDrCustomerId($digitalRiverCustomerId);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasDigitalRiverCustomerId(CustomerInterface $customer): bool
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $storeId = $customer->getStoreId();
        if($storeId === $currentStoreId){
            $drCustomerIdAttribute = $customer->getCustomAttribute(AddDrCustomerId::ATTRIBUTE_CODE);
            
            return $drCustomerIdAttribute && null !== $drCustomerIdAttribute->getValue();
        }else{
            return false;
        }
    }
}
