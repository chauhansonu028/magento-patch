<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{

    /**
     * @var \Digitalriver\DrPay\Helper\Data
     */
    private \Digitalriver\DrPay\Helper\Data $drHelper;

    /**
     * @var \Digitalriver\DrPay\Helper\Config 
     */
    private \Digitalriver\DrPay\Helper\Config $config;

    /**
     * @param \Digitalriver\DrPay\Helper\Data $drHelper
     */
    public function __construct(
        \Digitalriver\DrPay\Helper\Data $drHelper,
        \Digitalriver\DrPay\Helper\Config $config
    ) {
        $this->drHelper = $drHelper;
        $this->config = $config;
    }

    /**
     * Create DR customer, if necessary, to reduce setCustomer() calls within checkout
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getIsEnabled()) {
            $customer = $observer->getEvent()->getCustomer();
            $this->drHelper->setCustomer($customer->getEmail());
        }
    }
}
