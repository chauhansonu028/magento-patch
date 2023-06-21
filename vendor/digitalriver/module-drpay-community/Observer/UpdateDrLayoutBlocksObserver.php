<?php
/**
 * DrPay Observer
 */

namespace Digitalriver\DrPay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Digitalriver\DrPay\Helper\Config;

/**
 * Update Digitalriver layout blocks
 *
 */
class UpdateDrLayoutBlocksObserver implements ObserverInterface
{

    /**
     * @var Config
     */
    private $config;


    /**
     * UpdateDrLayoutBlocksObserver constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getLayout();
        if(!$this->config->getIsEnabled()){
            $layout->unsetElement('digital-river-tax-cert');
            $layout->unsetElement('digital-river-payment-methods');
        }else{
            $layout->unsetElement('customer-account-navigation-my-credit-cards-link');
            if(!$this->config->getIsTaxConfigEnabled()){
                $layout->unsetElement('digital-river-tax-cert');
            }
            if(!$this->config->getIsStoredMethodsEnabled()){
                $layout->unsetElement('digital-river-payment-methods');
            }            
        }
    }
}
