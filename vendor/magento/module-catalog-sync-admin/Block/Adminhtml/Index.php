<?php
/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSyncAdmin\Block\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Index extends Template
{
    /**
     * Config Paths
     * @var string
     */
    private const FRONTEND_URL_PATH = 'catalog_sync_admin/frontend_url';

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->servicesConfig = $servicesConfig;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Returns config for frontend url
     *
     * @return string
     */
    public function getFrontendUrl(): string
    {
        return (string) $this->_scopeConfig->getValue(
            self::FRONTEND_URL_PATH,
            ScopeInterface::SCOPE_WEBSITE
        );
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
     * Get Environment Id from Services Id configuration
     *
     * @return string|null
     */
    public function getEnvironmentId(): ?string
    {
        return $this->servicesConfig->getEnvironmentId();
    }

    /**
     * Get API Key from Services Connector configuration
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteCode(): ?string
    {
        $websiteId = $this->_storeManager->getStore($this->getStoreViewCode())->getWebsiteId();
        return $this->_storeManager->getWebsite($websiteId)->getCode();
    }

    /**
     * Get product count for selected store view
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreViewProductCount(): int
    {
        $productCollection = $this->collectionFactory->create();
        $productCollection->addStoreFilter($this->getStoreViewCode());
        return $productCollection->count();
    }
}
