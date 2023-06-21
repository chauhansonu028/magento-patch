<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model\DropIn;

use Digitalriver\DrPay\Helper\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const PAYMENT_METHOD_DROPIN_CODE = 'drpay_dropin';
    /**
     * @var string[]
     */
    protected $_methodCode = self::PAYMENT_METHOD_DROPIN_CODE;
    /**
     * $_method.
     *
     * @var Magento\Payment\Helper\Data
     */
    protected $_method;
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * __construct constructor.
     *
     * @param PaymentHelper $paymentHelper
     * @param Session       $checkoutSession
     * @param Escaper       $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        CheckoutSession $checkoutSession,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $enc,
        Config $config
    ) {
        $this->_method = $paymentHelper->getMethodInstance($this->_methodCode);
        $this->escaper = $escaper;
        $this->checkoutSession = $checkoutSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_enc = $enc;
        $this->config = $config;
    }

    /**
     * getConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        $store = $this->checkoutSession->getQuote()->getStore();
        $config = [
            'payment' => [
                'drpay_dropin' => [
                    'mage_locale' => $this->_scopeConfig->getValue(
                        'general/locale/code',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    ),
                    'public_key' => $this->_enc->decrypt(
                        $this->_scopeConfig->getValue(
                            'dr_settings/config/drapi_public_key',
                            ScopeInterface::SCOPE_STORE,
                            $store
                        )
                    ),
                    'is_active' => (bool) $this->config->getIsEnabled(),
                    'title' => $this->_scopeConfig->getValue(
                        'payment/drpay_dropin/title',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    ),
                    'default_selling_entity' => $this->config->getDefaultSellingEntity(),
                    'disable_automatic_redirects' => $this->config->getIsDisabledRedirect($store->getCode())
                ],
            ],
        ];
        if ($config['payment']['drpay_dropin']['mage_locale'] == "zh_Hant_TW") {
            $config['payment']['drpay_dropin']['mage_locale'] = 'zh_TW';
        } elseif ($config['payment']['drpay_dropin']['mage_locale'] == "zh_Hans_CN") {
            $config['payment']['drpay_dropin']['mage_locale'] = 'zh_CN';
        }
        $config['payment']['instructions'][$this->_methodCode] = $this->getInstructions($this->_methodCode);
        return $config;
    }
    /**
     * Get instructions text from config
     *
     * @param  string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->_method->getInstructions()));
    }
}
