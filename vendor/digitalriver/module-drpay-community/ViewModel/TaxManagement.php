<?php

namespace Digitalriver\DrPay\ViewModel;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Model\Customer\CustomerTaxCertificate;
use Digitalriver\DrPay\Model\DropIn\ConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Country;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Retrieves tax management page url and config
 */
class TaxManagement implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /** @var string  */
    private const TAX_MANAGEMENT_PAGE_URL = "drpay/customer/addnewcert";

    /** @var string  */
    public const CHECKOUT_REFER_PARAM_NAME = 'checkout_refer';

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerTaxCertificate
     */
    private $customerTaxCertificate;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Config $config
     * @param UrlInterface $urlInterface
     * @param ConfigProvider $configProvider
     * @param CustomerTaxCertificate $customerTaxCertificate
     * @param ManagerInterface $messageManager
     * @param Country $country
     * @param RequestInterface $request
     */
    public function __construct(
        Config $config,
        UrlInterface $urlInterface,
        ConfigProvider $configProvider,
        CustomerTaxCertificate $customerTaxCertificate,
        ManagerInterface $messageManager,
        Country $country,
        RequestInterface $request,
        Session $checkoutSession
    ) {
        $this->config = $config;
        $this->customerTaxCertificate = $customerTaxCertificate;
        $this->urlInterface = $urlInterface;
        $this->configProvider = $configProvider;
        $this->messageManager = $messageManager;
        $this->country = $country;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    public function getTaxManagementViewUrl():? string
    {
        return $this->urlInterface->getUrl(self::TAX_MANAGEMENT_PAGE_URL);
    }

    /**
     * @return string|null
     */
    public function getPublicKey():? string
    {
        return !empty($this->configProvider->getConfig()['payment']['drpay_dropin']['public_key']) ?
            (string) $this->configProvider->getConfig()['payment']['drpay_dropin']['public_key'] :
            null;
    }

    /**
     * @return string|null
     */
    public function getLocale():? string
    {
        return !empty($this->configProvider->getConfig()['payment']['drpay_dropin']['mage_locale']) ?
            (string) $this->configProvider->getConfig()['payment']['drpay_dropin']['mage_locale'] :
            null;
    }

    /**
     * @return string
     */
    public function getDefaultSellingEntity(): string
    {
        return (string)$this->config->getDefaultSellingEntity();
    }

    /**
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->urlInterface->getUrl('drpay/customer/newcertificate/');
    }

    /**
     * @return false|array
     */
    public function getTaxCertificateList()
    {
        try {
            $certificateData = $this->customerTaxCertificate->getDrCustomerTaxCertificate();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was an error fetching your list of certificates. Please try again later.')
            );

            $certificateData = [];
        }

        if (!$certificateData) {
            return [];
        }

        foreach ($certificateData as &$certificate) {
            $certificate['startDate'] = $this->dateFormatter($certificate['startDate']);
            $certificate['endDate'] = $this->dateFormatter($certificate['endDate']);
        }

        return $certificateData;
    }

    /**
     * @param $data
     * @return false|string
     */
    private function dateFormatter($data)
    {
        return date_format(date_create($data), 'M/d/Y');
    }

    /**
     * Returns list of regions to be used inside front-end select input (add-new-certificate-form.phtml)
     * @return Collection
     */
    public function getRegions()
    {
        $regionCollection = $this->country->loadByCode('US')->getRegions();
        return $regionCollection->loadData();
    }

    /**
     * @return bool
     */
    public function canGoBackToCheckout()
    {
        $paramValue = $this->request->getParam(self::CHECKOUT_REFER_PARAM_NAME, null);
        return !empty($paramValue) || (
                $this->checkoutSession->getQuote()
                && $this->checkoutSession->getQuote()->getItemsCount()
            );
    }
}
