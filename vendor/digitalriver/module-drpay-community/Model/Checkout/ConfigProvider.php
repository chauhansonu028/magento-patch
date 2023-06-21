<?php
/**
 * Provides DR Quote Error details
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Checkout;

use Digitalriver\DrPay\ViewModel\TaxManagement;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    private const TAX_CONFIG_ENABLED = "dr_settings/tax_conf/active";

    /** @var string  */
    private const CERTIFICATE_LIST_PAGE = "drpay/customer/index";

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'taxCertificatesListUrl' => $this->urlBuilder->getUrl(self::CERTIFICATE_LIST_PAGE, [
                TaxManagement::CHECKOUT_REFER_PARAM_NAME => 1
            ]),
            'taxManagementEnabled' => $this->getIsEnabled()
        ];
    }

    /**
     * @return bool
     */
    private function getIsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::TAX_CONFIG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
