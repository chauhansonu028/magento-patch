<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataServices\Block;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Cookie\Helper\Cookie;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\DataServices\Model\VersionFinderInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Context base block class
 *
 * @api
 */
class Context extends Template
{
    /**
     * Config paths
     */
    const EXTENSION_VERSION_CONFIG_PATH = 'dataservices/version';

    /**
     * Cache tags
     */
    const STOREFRONT_INSTANCE_CONTEXT_CACHE_TAG = 'dataservices_storefront_instance_';
    const CATALOG_EXPORTER_VERSION_CACHE_TAG = 'catalog_exporter_extension_version_';

    /**
     * Extension constants
     */
    const CATALOG_EXPORTER_MODULE_NAME = 'Magento/SaaSCommon';
    const CATALOG_EXPORTER_PACKAGE_NAME = 'magento/module-saas-common';

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CacheInterface
     */
    private $cacheInterface;

    /**
     * @var VersionFinderInterface
     */
    private $versionFinder;

    /**
     * @var Cookie
     */
    private $cookieHelper;

    /**
    * @var CustomerSession
    */
    private $customerSession;

    /**
     * @var null|string
     */
    private $accountEmail;

    /**
     * @param Template\Context $context
     * @param Json $jsonSerializer
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $config
     * @param ServicesConfigInterface $servicesConfig
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cacheInterface
     * @param VersionFinderInterface $versionFinder
     * @param Cookie $cookieHelper
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Json $jsonSerializer,
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $config,
        ServicesConfigInterface $servicesConfig,
        StoreManagerInterface $storeManager,
        CacheInterface $cacheInterface,
        VersionFinderInterface $versionFinder,
        Cookie $cookieHelper,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonSerializer = $jsonSerializer;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->servicesConfig = $servicesConfig;
        $this->storeManager = $storeManager;
        $this->cacheInterface = $cacheInterface;
        $this->versionFinder = $versionFinder;
        $this->cookieHelper = $cookieHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * Get context Json for events
     *
     * @return string
     */
    public function getEventContext(): string
    {
        $context = [];
        $viewModel = $this->getViewModel();
        if ($viewModel) {
            $context = $viewModel->getModelContext();
        }
        return $this->jsonSerializer->serialize($context);
    }

    /**
     * Return cart id for event tracking
     *
     * @return int
     */
    public function getCartId(): int
    {
        return (int) $this->checkoutSession->getQuoteId();
    }

    /**
     * Return coupon code for event tracking
     *
     * @return string|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCouponCode(): ?string
    {
        return (string) $this->checkoutSession->getQuote()->getCouponCode();
    }

    /**
     * Return storefront-instance context for data services events
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getStorefrontInstanceContext(): string
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        $context = $this->cacheInterface->load(self::STOREFRONT_INSTANCE_CONTEXT_CACHE_TAG . $storeId);

        if (!$context) {
            $website = $this->storeManager->getWebsite();
            $group = $this->storeManager->getGroup();
            $contextData = [
                'storeUrl' => $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
                'websiteId' => (int) $website->getId(),
                'websiteCode' => $website->getCode(),
                'storeId' => (int) $group->getId(),
                'storeCode' => $group->getCode(),
                'storeViewId' => (int) $store->getId(),
                'storeViewCode' => $store->getCode(),
                'websiteName' => $website->getName(),
                'storeName' => $group->getName(),
                'storeViewName' => $store->getName(),
                'baseCurrencyCode' => $store->getBaseCurrencyCode(),
                'storeViewCurrencyCode' => $store->getCurrentCurrency()->getCode(),
                'catalogExtensionVersion' => $this->getCatalogExtensionVersion()
            ];
            $context = $this->jsonSerializer->serialize($contextData);
            $this->cacheInterface->save($context, self::STOREFRONT_INSTANCE_CONTEXT_CACHE_TAG . $storeId);
        } else {
            $contextData = $this->jsonSerializer->unserialize($context);
        }

        $contextData['environmentId'] = $this->servicesConfig->getEnvironmentId();
        $contextData['environment'] = $this->servicesConfig->getEnvironmentType();
        return $this->jsonSerializer->serialize($contextData);
    }

    /**
     * Return magento-extension version for data services events
     *
     * @return string
     */
    public function getExtensionVersion(): string
    {
        return $this->config->getValue(self::EXTENSION_VERSION_CONFIG_PATH);
    }

    /**
     * Return catalog extension version if installed
     *
     * @return string|null
     */
    private function getCatalogExtensionVersion()
    {
        $catalogVersion = $this->cacheInterface->load(self::CATALOG_EXPORTER_VERSION_CACHE_TAG);
        if (!$catalogVersion) {
            $catalogVersion = $this->versionFinder->getVersionFromComposer(self::CATALOG_EXPORTER_PACKAGE_NAME);

            if (!$catalogVersion) {
                $catalogVersion = $this->versionFinder->getVersionFromFiles(
                    self::CATALOG_EXPORTER_MODULE_NAME,
                    self::CATALOG_EXPORTER_PACKAGE_NAME
                );
            }
            $this->cacheInterface->save($catalogVersion, self::CATALOG_EXPORTER_VERSION_CACHE_TAG);
        }
        return $catalogVersion ? $catalogVersion : null;
    }

    /**
     * @deprecad since 7.1.0
     * @see getCustomerEmail
     * Return Customer Email address
     *
     * @return string
     */
    public function getCustomerEmailAddress(): string
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * Returns Customer Email address.
     * Returns NULL if customer is not logged in.
     *
     * @return string|null
     */
    public function getCustomerEmail(): ?string
    {
        if (!$this->accountEmail) {
            $this->accountEmail = $this->customerSession->isLoggedIn()
                ? $this->customerSession->getCustomer()->getEmail()
                : null;
        }
        return $this->accountEmail;
    }

    /**
     * Return cookie restriction mode value.
     *
     * @return bool
     */
    public function isCookieRestrictionModeEnabled()
    {
        return $this->cookieHelper->isCookieRestrictionModeEnabled();
    }

    /**
     * Check if DataServices functionality should be enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->servicesConfig->isApiKeySet() && $this->servicesConfig->getEnvironmentId();
    }
}
