<?php declare(strict_types=1);
/**
 * Overrides DOM element on customer's admin order page to show DR payment type used on purchase
 *
 * @summary
 * Provides override template mapping and prepares payment definition string for fetch by template
 * @author Ninoslav Stojcic <ninoslav.stojcic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Block\Info;

/**
 * Block for DR payment generic info
 *
 */
class Instructions extends \Magento\Payment\Block\Info\Instructions
{
    /**
     * @var string
     */
    protected $_template = 'Digitalriver_DrPay::info/instructions.phtml';

    /**
     * @var string
     */
    protected $_cc;

    /**
     * @var string
     */
    protected $_sourceNames;

    /**
     * Get DR Credit Card Type code
     *
     * @return string
     */
    public function getDrCCType(): string
    {
        return $this->getInfo()->getCcType() ?? '';
    }

    /**
     * Returns last four digits of the credit card
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDrCCLast4(): string
    {
        return $this->getInfo()->getCcLast4() ?? '';
    }

    /**
     * Returns true if payment is of DR Creadit Card type
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isDrCC(): bool
    {
        //DIRI-170 DR payment is not set on place Order. Order Payment table is used instead.
        if ($this->getInfo()->getOrder()->getDrPaymentMethod()) {
            return ($this->getInfo()->getOrder()->getDrPaymentMethod() === 'CreditCard');
        } else {
            $paymentMethod = $this->getInfo()->getOrder()->getPayment()->getMethod();
            if ($paymentMethod === "drpay_dropin") {
                return ($this->getInfo()->getCcType() && trim($this->getInfo()->getCcType()) !== "");
            }
            return false;
        }
    }

    /**
     * Returns DR Payment Method
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDrPaymentMethod(): string
    {
        //DIRI-170 DR payment method is not set on place Order. Order Payment table is used instead.
        $drPaymentMethod = $this->getInfo()->getOrder()->getDrPaymentMethod();
        if ($drPaymentMethod && trim($drPaymentMethod) !== "") {
            return $drPaymentMethod;
        } else {
            $additionalInfo = $this->getInfo()->getAdditionalInformation();
            return (isset($additionalInfo['method'])) ? $additionalInfo['method'] : "";
        }
    }

    /**
     * Gets charges
     *
     * @return array
     */
    public function getSourceNames()
    {
        if ($this->_sourceNames === null) {
            $this->_sourceNames = $this->getInfo()->getAdditionalInformation(
                'sourceNames'
            );
        }
        return $this->_sourceNames;
    }
}
