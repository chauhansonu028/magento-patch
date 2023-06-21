<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Block\Adminhtml;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\LiveSearch\Api\ServiceClientInterface;
use Magento\LiveSearch\Block\BaseSaaSContext;
use Magento\LiveSearch\Model\ModuleVersionReader;
use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\ServicesConnector\Exception\PrivateKeySignException;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 */
class SaaSContext extends BaseSaaSContext
{
    /**
     * Config Paths
     *
     * @var string
     */
    private const FRONTEND_URL_PATH = 'live_search/frontend_url';

    /**
     * @var ServiceClientInterface
     */
    private ServiceClientInterface $serviceClient;

    /**
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     * @param ProductMetadata $productMetadata
     * @param ModuleVersionReader $moduleVersionReader
     * @param CurrencyInterface $localeCurrency
     * @param Session $customerSession
     * @param ServiceClientInterface $serviceClient
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig,
        ProductMetadata $productMetadata,
        ModuleVersionReader $moduleVersionReader,
        CurrencyInterface $localeCurrency,
        Session $customerSession,
        ServiceClientInterface $serviceClient
    ) {
        $this->serviceClient = $serviceClient;
        parent::__construct(
            $context,
            $servicesConfig,
            $productMetadata,
            $moduleVersionReader,
            $localeCurrency,
            $customerSession
        );
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
     * Check if API key is valid
     *
     * @return bool
     */
    public function isApiKeyValid(): bool
    {
        try {
            return $this->serviceClient->isApiKeyValid();
        } catch (KeyNotFoundException | PrivateKeySignException $exception) {
            return false;
        }
    }
}
