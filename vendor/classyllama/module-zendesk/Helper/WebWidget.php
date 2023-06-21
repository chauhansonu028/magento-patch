<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class WebWidget extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const WEB_WIDGET_SNIPPET_CACHE_CONFIG_PATH = 'zendesk/web_widget/saved_widget_snippet';

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * WebWidget constructor.
     * @param Context $context
     * @param Config $configHelper
     * @param ZendClientFactory $httpClientFactory
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        // End parent parameters
        \Zendesk\Zendesk\Helper\Config $configHelper,
        ZendClientFactory $httpClientFactory, // @phpstan-ignore-line
        WriterInterface $configWriter,
        Manager $cacheManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->httpClientFactory = $httpClientFactory;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get web widget snippet by making live query to Zendesk API.
     *
     * @param string $domain
     * @return string
     */
    protected function doGetWebWidgetSnippet($domain)
    {
        $url = str_replace(
            \Zendesk\Zendesk\Helper\Config::DOMAIN_PLACEHOLDER,
            $domain,
            $this->configHelper->getWebWidgetDynamicSnippetUrlPattern()
        );

        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create(['uri' => $url]);

        try {
            $response = $httpClient->request();

            return $response->getBody();
        } catch (\Exception $ex) {
            // Intentionally swallow exception to avoid breaking pages.

            return '';
        }
    }

    /**
     * Get web widget snippet saved in config, if any.
     *
     * @param string $subdomain
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    protected function getConfigSavedSnippet(
        $subdomain,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return (string)$this->scopeConfig->getValue(
            self::WEB_WIDGET_SNIPPET_CACHE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Persist web widget snippet in hidden config field value to cache value indefinitely.
     *
     * @param string $subdomain
     * @param string $snippet
     * @param string $scopeType
     * @param int $scopeCode
     * @throws NoSuchEntityException
     */
    protected function setConfigSavedSnippet(
        $subdomain,
        $snippet,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = 0
    ) {
        if ($this->storeManager->getStore()->getStoreId()) {
            $scopeCode = $this->storeManager->getStore()->getStoreId();
            $scopeType = ScopeInterface::SCOPE_STORES;
        }
        $this->configWriter->save(
            self::WEB_WIDGET_SNIPPET_CACHE_CONFIG_PATH,
            $snippet,
            $scopeType,
            (int)$scopeCode
        );

        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }

    /**
     * Get web widget snippet, taking saved configured value into account.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     * @throws NoSuchEntityException
     */
    public function getWebWidgetSnippet(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($this->storeManager->getStore()->getStoreId()) {
            $scopeCode = $this->storeManager->getStore()->getStoreId();
            $scopeType = ScopeInterface::SCOPE_STORES;
        }
        // Get proper scope type and scope code
        $domain = $this->configHelper->getDomain($scopeType, $scopeCode);

        if (empty($domain)) {
            return ''; // Zendesk domain not configured.
        }

        // Since subdomain is a function of domain, it must be configured at this point

        $subdomain = $this->configHelper->getSubDomain($scopeType, $scopeCode);
        // Make full domain if necessary
        if ($subdomain === $domain) {
            $domain = $subdomain . ".zendesk.com";
        }

        $snippet = $this->getConfigSavedSnippet($subdomain, $scopeType, $scopeCode);

        if (empty($snippet) || $snippet === " ") {
            // Must be the first time the snippet has been requested.
            // Perform live lookup, then save value in config.

            $snippet = $this->doGetWebWidgetSnippet($domain);
            $this->setConfigSavedSnippet($subdomain, $snippet, $scopeType, $scopeCode);
        }

        return $snippet;
    }

    /**
     * Enable web widget configuration setting
     *
     * @param bool $enabled
     * @param string $scopeType
     * @param int $scopeId
     */
    public function toggleWebWidget(
        $enabled,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        // NB: it would be a lot cleaner to simply save the value of $enabled.
        // However, this could leave the setting explicitly disabled at a store view scope
        // if later enabled at a global scope, which could be confusing for the user.
        // Since the default value in config.xml is disabled, removing the explicit value
        // in the case it is intended to be disabled results in a more intuitive user experience.

        if ($enabled) {
            $this->configWriter->save(
                \Zendesk\Zendesk\Helper\Config::WEB_WIDGET_ENABLED_CONFIG_PATH,
                1,
                $scopeType,
                $scopeId
            );
        } else {
            $this->configWriter->delete(
                \Zendesk\Zendesk\Helper\Config::WEB_WIDGET_ENABLED_CONFIG_PATH,
                $scopeType,
                $scopeId
            );
        }

        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }
}
