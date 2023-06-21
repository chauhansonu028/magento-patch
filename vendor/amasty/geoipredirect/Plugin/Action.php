<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Plugin;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\Base\Model\MagentoVersion;
use Amasty\Base\Model\Serializer;
use Amasty\Geoip\Model\Geolocation;
use Amasty\Geoip\Helper\Data as GeoipHelper;
use Amasty\GeoipRedirect\Helper\Data as GeoipRedirectHelper;
use Amasty\GeoipRedirect\Model\ConfigProvider;
use Amasty\GeoipRedirect\Model\RedirectUrl\UrlProcessor;
use Amasty\GeoipRedirect\Model\Source\Logic;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Router\Base;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Action
{
    public const URL_TRIM_CHARACTER = '/';
    public const HOME = 'cms_index_index';
    public const FIRST_REDIRECT_WITH_POPUP = null;

    /**
     * @var bool
     */
    private $redirectAllowed = false;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GeoipHelper
     */
    private $geoipHelper;

    /**
     * @var GeoipRedirectHelper
     */
    private $geoipRedirectHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var Http
     */
    private $response;

    /**
     * @var SessionManagerInterface $sessionManager
     */
    private $sessionManager;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var Base
     */
    private $baseRouter;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var UrlProcessor
     */
    private $urlProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetCustomerIp
     */
    private $customerIp;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GeoipHelper $geoipHelper,
        GeoipRedirectHelper $geoipRedirectHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        Geolocation $geolocation,
        Session $customerSession,
        StoreCookieManagerInterface $storeCookieManager,
        RedirectFactory $resultRedirectFactory,
        Http $response,
        SessionManagerInterface $sessionManager,
        ResolverInterface $resolver,
        Serializer $serializer,
        UrlFinderInterface $urlFinder,
        Base $baseRouter,
        MagentoVersion $magentoVersion,
        UrlProcessor $urlProcessor,
        ConfigProvider $configProvider,
        GetCustomerIp $customerIp
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->geoipHelper = $geoipHelper;
        $this->geoipRedirectHelper = $geoipRedirectHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->geolocation = $geolocation;
        $this->customerSession = $customerSession;
        $this->storeCookieManager = $storeCookieManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->response = $response;
        $this->sessionManager = $sessionManager;
        $this->resolver = $resolver;
        $this->serializer = $serializer;
        $this->urlFinder = $urlFinder;
        $this->baseRouter = $baseRouter;
        $this->magentoVersion = $magentoVersion;
        $this->urlProcessor = $urlProcessor;
        $this->configProvider = $configProvider;
        $this->customerIp = $customerIp;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundDispatch(
        FrontControllerInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $scopeStores = ScopeInterface::SCOPE_STORES;

        if (!$this->scopeConfig->isSetFlag('amgeoipredirect/general/enable', $scopeStores, $currentStoreId)
            || $this->isNeedToProceed($request)
        ) {
            return $proceed($request);
        }

        $session = $this->sessionManager;
        $countRedirectStore = $countRedirectCurrency = $countRedirectUrl = 0;
        $isNotFirstTime = null;
        $changeCurrency = false;

        if ($this->scopeConfig->getValue('amgeoipredirect/restriction/first_visit_redirect')) {
            // session value getters should be before processed request, otherwise will return null with FPC enabled
            $isNotFirstTime = $session->getIsNotFirstTime();
            $countRedirectStore = $session->getAmYetRedirectStore();
            $countRedirectCurrency = $session->getAmYetRedirectCurrency();
            $countRedirectUrl = $session->getAmYetRedirectUrl();
            $session->setIsNotFirstTime(1);
        }
        $this->applyLogic($request);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->scopeConfig->getValue('amgeoipredirect/general/enable', $scopeStores, $currentStoreId)
            || !$this->redirectAllowed
        ) {
            return $proceed($request);
        }

        $currentIp = $this->getCurrentIp();

        if ($this->isIpBlock($currentIp)) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $page = $this->scopeConfig->getValue(
                'amgeoipredirect/restrict_by_ip/cms_to_show',
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            $url = $this->urlBuilder->getUrl($page);
            if (rtrim($this->urlProcessor->parseUrl($url)['path'], '/')
                !== rtrim($this->urlProcessor->parseUrl($this->urlBuilder->getCurrentUrl())['path'], '/')
            ) {
                $this->response->setNoCacheHeaders();
                return $resultRedirect->setUrl($url);
            }
        }
        $location = $this->geolocation->locate($currentIp);
        $country = $location->getCountry();

        if (!$countRedirectCurrency
            && !$isNotFirstTime
            && $this->scopeConfig->getValue('amgeoipredirect/country_currency/enable_currency')
        ) {
            $changeCurrency = true;
        }

        if (!$countRedirectUrl
            && !$isNotFirstTime
            && $this->scopeConfig->getValue('amgeoipredirect/country_url/enable_url')
            && $country
        ) {
            $urlMapping = $this->serializer->unserialize(
                $this->scopeConfig->getValue(
                    'amgeoipredirect/country_url/url_mapping',
                    $scopeStores,
                    $currentStoreId
                )
            );

            $currentUrl = trim($this->urlBuilder->getCurrentUrl(), self::URL_TRIM_CHARACTER);

            foreach ($urlMapping as $countries => $url) {
                $url = trim($url, self::URL_TRIM_CHARACTER);

                if (strpos($countries, $country) !== false && $url != $currentUrl) {
                    $session->setAmYetRedirectUrl(1);
                    $this->response->setNoCacheHeaders();

                    if ($this->needShowRedirectionPopup()) {
                        $session->setAmYetRedirectUrl(null);
                        $session->setIsNotFirstTime(self::FIRST_REDIRECT_WITH_POPUP);
                        $session->setAmPopupCountry($country);
                        return $proceed($request);
                    }

                    if ($this->sessionManager->getWillRedirect() !== false) {
                        return $resultRedirect->setUrl($url);
                    }
                }
            }
        }

        if (!$countRedirectStore
            && !$isNotFirstTime
            && $this->scopeConfig->getValue('amgeoipredirect/country_store/enable_store')
        ) {
            $allStores = $this->storeManager->getStores();
            foreach ($allStores as $store) {
                $currentStoreUrl = str_replace('&amp;', '&', $store->getCurrentUrl(false));
                $redirectStoreUrl = trim($currentStoreUrl, '/');

                $countries = $this->scopeConfig->getValue(
                    'amgeoipredirect/country_store/affected_countries',
                    $scopeStores,
                    $store->getId()
                );
                if (!$this->scopeConfig->getValue('amgeoipredirect/restriction/redirect_between_websites')) {
                    $useMultistores = $store->getWebsiteId() == $this->storeManager->getStore()->getWebsiteId();
                } else {
                    $useMultistores = true;
                }

                if ($country && $countries && strpos($countries, $country) !== false
                    && $store->getId() != $currentStoreId
                    && $useMultistores
                ) {
                    $currentUrl = $this->urlBuilder->getCurrentUrl();
                    $neededBaseUrl = $store->getBaseUrl();

                    if ((strpos($currentUrl, $neededBaseUrl) === false)
                        || !$this->_compareEqualUrlFromStore($request, $store)
                    ) {
                        if ($changeCurrency) {
                            $this->_setNewCurrencyIfExist($country, $scopeStores, $store->getId());
                        }
                        $redirectStoreUrl = $this->urlProcessor->updateRedirectUrlQueryParams(
                            $redirectStoreUrl,
                            $request,
                            $this->storeManager->getStore(),
                            $store
                        );

                        if ($this->needShowRedirectionPopup()) {
                            $session->setAmYetRedirectStore(null);
                            $session->setIsNotFirstTime(self::FIRST_REDIRECT_WITH_POPUP);
                            $session->setRedirectStoreId($store->getId());
                            $session->setAmPopupCountry($country);

                            return $proceed($request);
                        } elseif ($this->sessionManager->getWillRedirect() !== false) {
                            $this->_setDefaultLocale($store);
                            $session->setAmYetRedirectStore(1);
                            $this->storeCookieManager->setStoreCookie($store);
                            $this->response->setNoCacheHeaders();

                            return $resultRedirect->setUrl(
                                $this->tryReplaceWithUrlRewrite($request, $redirectStoreUrl, $store->getId())
                            );
                        }
                    }
                }
            }
        }

        if ($changeCurrency && !empty($country)) {
            $this->_setNewCurrencyIfExist($country, $scopeStores, $currentStoreId);
        }

        return $proceed($request);
    }

    /**
     * @param RequestInterface $request
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function tryReplaceWithUrlRewrite(RequestInterface $request, string $url, int $storeId): string
    {
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => ltrim($request->getPathInfo(), '/'),
            UrlRewrite::STORE_ID => $this->storeManager->getStore()->getId(),
        ]);

        if ($rewrite) {
            $rewriteToOtherStore = $this->urlFinder->findOneByData([
                UrlRewrite::TARGET_PATH => $rewrite->getTargetPath(),
                UrlRewrite::STORE_ID => $storeId,
            ]);

            if ($rewriteToOtherStore) {
                return str_replace(
                    ltrim($request->getPathInfo(), '/'),
                    $rewriteToOtherStore->getRequestPath(),
                    $url
                );
            }
        }

        return $url;
    }

    protected function needShowRedirectionPopup()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $needPopup = $this->scopeConfig->getValue(
            'amgeoipredirect/general/redirection_decline',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($needPopup && $this->sessionManager->getNeedShow() !== false) {
            $this->sessionManager->setNeedShow(true);

            return true;
        }

        return false;
    }

    /**
     * @param StoreInterface $store
     * @return bool
     */
    protected function _setDefaultLocale($store)
    {
        if ($store->getId()) {
            $localeCode = $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
            $this->resolver->setDefaultLocale($localeCode)->setLocale($localeCode);
        } else {
            return false;
        }
    }

    /**
     * @param $country
     * @param $scopeStores
     * @param $currentStoreId
     * @return $this
     */
    protected function _setNewCurrencyIfExist($country, $scopeStores, $currentStoreId)
    {
        $currencyMapping = $this->serializer->unserialize(
            $this->scopeConfig->getValue(
                'amgeoipredirect/country_currency/currency_mapping',
                $scopeStores,
                $currentStoreId
            )
        );

        foreach ($currencyMapping as $countries => $currency) {
            if (strpos($countries, $country) !== false
                && $this->storeManager->getStore()
                    ->getCurrentCurrencyCode() != $currency
            ) {
                $this->sessionManager->setAmYetRedirectCurrency(1);
                $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
            }
        }

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @param $checkStore
     * @return bool
     */
    protected function _compareEqualUrlFromStore($request, $checkStore)
    {
        if (version_compare($this->magentoVersion->get(), '2.3.0', '>=')) {
            $param = StoreManagerInterface::PARAM_NAME;
        } else {
            $param = StoreResolverInterface::PARAM_NAME;
        }
        if ($request->getParam($param)) {
            return ($checkStore
                && ($request->getParam($param) != $checkStore->getCode()))
                ? false : true;
        }

        return false;
    }

    /**
     * Is redirect allowed
     *
     * @param $request
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    protected function applyLogic($request)
    {
        $applyLogic = $this->scopeConfig->getValue('amgeoipredirect/restriction/apply_logic');
        $currentUrl = rtrim($this->urlBuilder->getCurrentUrl(), '/') . '/';

        switch ($applyLogic) {
            case Logic::SPECIFIED_URLS:
                $acceptedUrls = explode(
                    PHP_EOL,
                    $this->scopeConfig->getValue('amgeoipredirect/restriction/accepted_urls')
                );

                foreach ($acceptedUrls as $url) {
                    $url = trim($url);

                    if ($url && $currentUrl && $this->_compareUrls($currentUrl, $url)) {
                        $this->redirectAllowed = true;
                        return $url;
                    }
                }
                break;
            case Logic::HOMEPAGE_ONLY:
                if ($this->isHomePage($request)) {
                    $this->redirectAllowed = true;
                }
                break;
            default:
                $exceptedUrls = $this->configProvider->getExceptedUrls();

                foreach ($exceptedUrls as $url) {
                    $url = trim($url);

                    if ($url && $currentUrl && strpos($currentUrl, $url) !== false) {
                        $this->redirectAllowed = false;

                        return $url;
                    } else {
                        $this->redirectAllowed = true;
                    }
                }
        }

        return '';
    }

    /**
     * @param string $currentUrl
     * @param string $comapareUrl
     * @return bool
     */
    protected function _compareUrls($currentUrl, $comapareUrl)
    {
        $urlParse = $this->urlProcessor->parseUrl($comapareUrl);
        $currentUrlParse = $this->urlProcessor->parseUrl($currentUrl);

        return (is_array($urlParse)
            && is_array($currentUrlParse)
            && (!isset($urlParse['host']) || $urlParse['host'] === $currentUrlParse['host'])
            && (
                !isset($urlParse['path']) && !isset($currentUrlParse['path'])
                || (isset($urlParse['path'])
                    && isset($currentUrlParse['path'])
                    && str_replace('/', '', $urlParse['path']) === str_replace('/', '', $currentUrlParse['path']))
            )
        );
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    protected function isHomePage($request)
    {
        $cloneRequest = clone $request;
        $this->baseRouter->match($cloneRequest);

        return $cloneRequest->getFullActionName() === self::HOME;
    }

    /**
     * Is redirect by GeoIP has not needed
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    protected function isNeedToProceed($request)
    {
        if ($this->isIpRestricted()
            || $request->isXmlHttpRequest()
        ) {
            return true;
        }

        $isApi = $request->getControllerModule() == 'Mage_Api';

        if ($isApi || !$this->geoipRedirectHelper->isModuleOutputEnabled('Amasty_Geoip')) {
            return true;
        }

        $userAgent = $request->getHeader('USER_AGENT');
        $userAgentsIgnore = $this->scopeConfig->getValue('amgeoipredirect/restriction/user_agents_ignore');

        if ($userAgent && !empty($userAgentsIgnore)) {
            $userAgentsIgnore = array_map("trim", explode(',', $userAgentsIgnore));

            foreach ($userAgentsIgnore as $agent) {
                if ($agent && stripos($userAgent, $agent) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isIpRestricted(): bool
    {
        $ipRestriction = $this->scopeConfig->getValue('amgeoipredirect/restriction/ip_restriction');
        $currentIp = $this->getCurrentIp();

        if ($currentIp && !empty($ipRestriction)) {
            $ipRestriction = array_map("rtrim", explode(PHP_EOL, $ipRestriction));

            foreach ($ipRestriction as $ip) {
                if ($currentIp == $ip) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $userIp
     * @return bool
     * @throws LocalizedException
     */
    private function isIpBlock($userIp)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $configIpsToBlock = $this->scopeConfig->getValue("amgeoipredirect/restrict_by_ip/ip_to_block");
        $websiteIpsToBlock = $this->scopeConfig->getValue(
            "amgeoipredirect/restrict_by_ip/ip_to_block",
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        if (empty($websiteIpsToBlock) && empty($configIpsToBlock)) {
            return false;
        }

        $ipsWeb = $websiteIpsToBlock ? preg_split('/\n|\r\n?/', $websiteIpsToBlock) : [];
        $ipsConfig = $configIpsToBlock ? preg_split('/\n|\r\n?/', $configIpsToBlock) : [];
        $ips = array_unique(array_merge($ipsWeb, $ipsConfig));

        foreach ($ips as $ip) {
            if (trim($ip) === $userIp) {
                return true;
            }
        }

        return false;
    }

    private function getCurrentIp(): string
    {
        if ($this->geoipHelper->isForcedIpEnabled() && $this->geoipHelper->getForcedIp()) {
            return $this->geoipHelper->getForcedIp();
        }

        return $this->customerIp->getCurrentIp();
    }
}
