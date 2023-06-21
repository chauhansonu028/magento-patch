<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Block;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\LiveSearch\Model\ModuleVersionReader;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\ScopeInterface;

class BaseSaaSContext extends Template
{
    /**
     * @var ServicesConfigInterface
     */
    private ServicesConfigInterface $servicesConfig;

    /**
     * @var ProductMetadata
     */
    private ProductMetadata $productMetadata;

    /**
     * @var ModuleVersionReader
     */
    private ModuleVersionReader $moduleVersionReader;

    /**
     * @var CurrencyInterface
     */
    private CurrencyInterface $localeCurrency;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * Autocomplete limit
     *
     * @var string
     */
    private const AUTOCOMPLETE_LIMIT = 'catalog/search/autocomplete_limit';

    /**
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     * @param ProductMetadata $productMetadata
     * @param ModuleVersionReader $moduleVersionReader
     * @param CurrencyInterface $localeCurrency
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig,
        ProductMetadata $productMetadata,
        ModuleVersionReader $moduleVersionReader,
        CurrencyInterface $localeCurrency,
        Session $customerSession
    ) {
        $this->servicesConfig = $servicesConfig;
        $this->productMetadata = $productMetadata;
        $this->moduleVersionReader = $moduleVersionReader;
        $this->localeCurrency = $localeCurrency;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Get website code from store view code
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getWebsiteCode(): string
    {
        $storeId = $this->getRequest()->getParam('store');
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        return $this->_storeManager->getWebsite($websiteId)->getCode();
    }

    /**
     * Get store code from store view code.
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        $storeId = $this->getRequest()->getParam('store');
        $storeGroupId = $this->_storeManager->getStore($storeId)->getStoreGroupId();
        return $this->_storeManager->getGroup($storeGroupId)->getCode();
    }

    /**
     * Get store view code from store switcher
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreViewCode(): string
    {
        $storeId = $this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId)->getCode();
    }

    /**
     * Get store currency symbol
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws CurrencyException
     */
    public function getCurrencySymbol(): string
    {
        $storeId = $this->getRequest()->getParam('store');
        $currencyCode = $this->_storeManager->getStore($storeId)->getCurrentCurrency()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);
        $currencySymbol = $currency->getSymbol() ?: $currency->getShortName();

        if (empty($currencySymbol)) {
            return $currencyCode;
        }

        return $currencySymbol;
    }

    /**
     * Get Environment Id from Services Id configuration
     *
     * @return string|null
     */
    public function getEnvironmentId(): ?string
    {
        return $this->servicesConfig->getEnvironmentId();
    }

    /**
     * Get Magento edition
     *
     * @return string|null
     */
    public function getMagentoEdition(): ?string
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Get Magento version
     *
     * @return string|null
     */
    public function getMagentoVersion(): ?string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Get module version
     *
     * @return string|null
     */
    public function getModuleVersion(): ?string
    {
        return $this->moduleVersionReader->getVersion();
    }

    /**
     * Get customer group code.
     *
     * @return string
     */
    public function getCustomerGroupCode(): string
    {
        try {
            // Using customerSession to get customer group id
            // Created ticket https://jira.corp.adobe.com/browse/AC-6741 for UserContextInterface
            $customerGroupId = $this->customerSession->getCustomerGroupId();
        } catch (LocalizedException $e) {
            $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
        }

        // sha1 is used in exported data, so we need to pass the same format here
        // https://github.com/magento-commerce/commerce-data-export-ee/blob/302dbb6216373110a8d2a3e636d6d8924f5e0f96/ProductOverrideDataExporter/etc/query.xml#L13
        return \sha1((string)$customerGroupId);
    }

    /**
     * Returns autocomplete limit from config
     *
     * @return int
     */
    public function getAutocompleteLimit(): int
    {
        return (int)$this->_scopeConfig->getValue(
            self::AUTOCOMPLETE_LIMIT,
            ScopeInterface::SCOPE_STORES
        );
    }
}
