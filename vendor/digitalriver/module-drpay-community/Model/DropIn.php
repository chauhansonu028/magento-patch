<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model;

/**
 * Implements business logic behind Digital River payment methods.
 *
 */
class DropIn extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_DROPIN_CODE = 'drpay_dropin';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_DROPIN_CODE;

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Digitalriver\DrPay\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Get instructions text from config
     *
     * @FIXME Refactor to no longer use the deprecated method
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions') ?? '');
    }

    /**
     * Retrieve block type for display method information
     *
     * @return string
     * @api
     * @deprecated 100.2.0
     */
    public function getInfoBlockType()
    {
        return $this->_infoBlockType;
    }
}
