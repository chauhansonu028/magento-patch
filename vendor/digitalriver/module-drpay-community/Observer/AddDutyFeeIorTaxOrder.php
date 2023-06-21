<?php declare(strict_types=1);
/**
 * Add Duty Fee and IOR Tax in sales_order before place an order.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Helper\Config;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddDutyFeeIorTaxOrder implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AddDutyFeeIorTaxOrder constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Add duty fee and IOR Tax to sales_order if DR API response contains duty fee and IOR tax value
     *
     * @param Observer $observer
     * @return $this
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->getIsEnabled()) {
            return $this;
        }

        $event = $observer->getEvent();

        $order = $event->getOrder();
        $quote = $event->getQuote();

        if ($quote->getIsDrIorSet()) {
            $order->setDrDutyFee($quote->getDrDutyFee());
            $order->setBaseDrDutyFee($quote->getBaseDrDutyFee());
            $order->setDrIorTax($quote->getDrIorTax());
            $order->setBaseDrIorTax($quote->getBaseDrIorTax());
        }
        return $this;
    }
}
