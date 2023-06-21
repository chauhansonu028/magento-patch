<?php
/**
 * Provides value for Duty Free coming from an DR API call
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Total\Quote;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Calculate total and fetch Duty fee
 * Class DutyFee
 */
class DutyFee extends AbstractTotal
{
    /**
     * @var Session
     */
    private $_checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Config
     */
    private $config;

    /**
     * DutyFee constructor.
     * @param Session $checkoutSession
     * @param Data $helper
     * @param Config $config
     */
    public function __construct(
        Session $checkoutSession,
        Data $helper,
        Config $config
    ) {
        $this->setCode('dr_duty_fee');
        $this->_checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param Quote $quote
     * @param Total $total
     * @return array|int
     */
    public function fetch(
        Quote $quote,
        Total $total
    ) {
        $result = null;
        $amount = $quote->getDrDutyFee();

        $result = [
            'code' => $this->getCode(),
            'title' => __('Duty Fee'),
            'value' => $amount ? $amount : 0,
        ];

        return $result;
    }
}
