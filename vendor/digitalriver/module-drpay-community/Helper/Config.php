<?php
/**
 * Config Helper
 */

namespace Digitalriver\DrPay\Helper;

use Digitalriver\DrPay\Api\HttpClientInterface;
use Digitalriver\DrPay\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Session\SessionManager;
use Magento\Store\Model\Information;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Configuration Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    private const XML_PATH_STORE_INFO_NAME = 'general/store_information/name';

    private const XML_PATH_STORE_INFO_PHONE = 'general/store_information/phone';

    private const XML_PATH_STORE_INFO_HOURS = 'general/store_information/hours';

    private const XML_PATH_STORE_INFO_STREET_LINE1 = 'shipping/origin/street_line1';

    private const XML_PATH_STORE_INFO_STREET_LINE2 = 'shipping/origin/street_line2';

    private const XML_PATH_STORE_INFO_CITY = 'shipping/origin/city';

    private const XML_PATH_STORE_INFO_POSTCODE = 'shipping/origin/postcode';

    private const XML_PATH_STORE_INFO_REGION_CODE = 'shipping/origin/region_id';

    private const XML_PATH_STORE_INFO_COUNTRY_CODE = 'shipping/origin/country_id';

    private const XML_PATH_STORE_INFO_VAT_NUMBER = 'general/store_information/merchant_vat_number';

    private const XML_PATH_COUNTRY_CODE_PATH = 'general/country/default';

    private const XML_PATH_OPTIONAL_ZIP_COUNTRIES = 'general/country/optional_zip_countries';

    private const XML_PATH_ALLOWED_COUNTRIES = 'general/country/allow';

    private const XML_PATH_STATE_REQUIRED_COUNTRIES = 'general/region/state_required';

    private const XML_PATH_ENABLE_DRPAY = 'dr_settings/config/active';

    private const XML_PATCH_DISABLE_REDIRECT = 'dr_settings/config/disable_redirect';

    private const XML_PATH_DRAPI_BASE_URL = 'https://api.digitalriver.com';

    private const XML_PATH_DROPIN_JS_URL = 'https://js.digitalriverws.com/v1/DigitalRiver.js';

    private const XML_PATH_DROPIN_CSS_URL = 'https://js.digitalriverws.com/v1/css/DigitalRiver.css';

    private const XML_PATH_DRAPI_PUBLIC_KEY = 'dr_settings/config/drapi_public_key';

    private const XML_PATH_DRAPI_SECRET_KEY = 'dr_settings/config/drapi_secret_key';

    private const XML_PATH_DRAPI_SIGNING_SECRET = 'dr_settings/config/drapi_signing_secret';

    private const XML_PATH_LOCALE = 'general/locale/code';

    private const XML_PATH_PRICE_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

    private const XML_PATH_SHIPPING_INCLUDES_TAX = 'tax/calculation/shipping_includes_tax';

    private const XML_PATH_CATALOG_SYNC_ENABLE = 'dr_settings/catalog_sync/active';

    private const XML_PATH_CATALOG_SYNC_BUNCH_SIZE = 'dr_settings/catalog_sync/batch_limit';

    private const XML_PATH_CATALOG_SYNC_DEBUG_MODE = 'dr_settings/catalog_sync/debug_mode';

    private const XML_PATH_SKU_GROUP_TTL = 'dr_settings/catalog_sync/dr_sku_group_api_ttl';

    private const CONNECTOR_VERSION = '3.1.3';

    private const DEFAULT_SELLING_ENTITY = 'dr_settings/config/default_selling_entity';

    private const DEFAULT_BATCH_SIZE = 250;

    private const XML_PATH_TAX_CONFIG_ACTIVE = 'dr_settings/tax_conf/active';

    private const XML_PATH_STORED_METHODS_ENABLED = 'dr_settings/stored_methods/enable';

    /**
     * @var HttpClientInterface
     */
    private $curl;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Information
     */
    private $storeInfo;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var EncryptorInterface
     */
    private $enc;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    public function __construct(
        Context $context,
        HttpClientInterface $curl,
        StoreManagerInterface $storeManager,
        Information $storeInfo,
        Session $session,
        CurrencyFactory $currencyFactory,
        Logger $logger,
        RegionFactory $regionFactory,
        Country $country,
        EncryptorInterface $enc,
        PriceCurrencyInterface $priceCurrency,
        Json $jsonSerializer,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->storeManager = $storeManager;
        $this->storeInfo = $storeInfo;
        $this->session = $session;
        $this->currencyFactory = $currencyFactory;
        $this->priceCurrency = $priceCurrency;
        $this->regionFactory = $regionFactory;
        $this->country = $country;
        $this->enc = $enc;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->productMetadata = $productMetadata;
        $this->sessionManager = $sessionManager;
        $this->remoteAddress = $remoteAddress;
    }

    public function convertToBaseCurrency($price)
    {
        $currentCurrency = $this->getCurrentCurrencyCode();
        $baseCurrency = $this->getBaseCurrencyCode();
        $rate = $this->currencyFactory->create()->load($currentCurrency)->getAnyRate($baseCurrency);
        $returnValue = $this->round($price * $rate);
        return $returnValue;
    }

    public function clearSessionData()
    {
        $this->session->unsDrQuoteError();
        $this->session->unsDrAccessToken();
        $this->session->unsSessionCheckSum();
        $this->session->unsDrCheckoutBillingChecksum();
        $this->session->unsDrCheckoutItemChecksum();
        $this->session->unsDrResult();
        $this->session->unsGuestCustomerEmail();
        $this->session->unsDrCustomerId();
        $this->session->unsDrCheckoutId();
        $this->session->unsDrLockedInCheckoutId();
        $this->session->unsDrSourceId();
        $this->session->unsIsDrPrimarySourceAssociatedWithCheckout();
        $this->session->unsDrPaymentSessionId();
        $this->session->unsDrSellingEntity();
        $this->session->unsDrReadyForStorage();

        $this->session->unsDrTax();

        $this->session->unsDrProductTax();
        $this->session->unsDrProductTotal();
        $this->session->unsDrProductTotalExcl();

        $this->session->unsDrShippingTax();
        $this->session->unsDrShippingAndHandling();
        $this->session->unsDrShippingAndHandlingExcl();

        $this->session->unsDrOrderTotal();
        $this->session->unsIsDrIorSet();
    }

    public function isTaxInclusive($storecode = null)
    {
        return $this->getConfig(self::XML_PATH_PRICE_INCLUDES_TAX, $storecode);
    }

    public function getDefaultSellingEntity()
    {
        $defaultSellingEntity = $this->getConfig(self::DEFAULT_SELLING_ENTITY);
        return isset($defaultSellingEntity) ? $defaultSellingEntity : "DR_INC-ENTITY";
    }

    public function getPurchaseLocation($address)
    {
        $result = [];
        $countryId = $address->getCountryId();
        if (empty($countryId)) {
            $countryId = $this->getDefaultCountry();
        }
        $result['country'] = $countryId;

        $regionName = $address->getRegion();
        $state = $this->getRegionCodeByNameAndCountryId($regionName, $countryId);
        if ($state) {
            $result['state'] = $state;
        }
        if (!empty($postalCode = $address->getPostCode())) {
            $result['postalCode'] = $postalCode;
        }
        return $result;
    }

    public function getDrStoreInfo()
    {
        $address['address']['line1'] = $this->getConfig(self::XML_PATH_STORE_INFO_STREET_LINE1);
        $address['address']['line2'] = '';
        $address['address']['city'] = $this->getConfig(self::XML_PATH_STORE_INFO_CITY);
        $address['address']['country'] = $this->getConfig(self::XML_PATH_STORE_INFO_COUNTRY_CODE);
        $regionStoreCode = $this->getConfig(self::XML_PATH_STORE_INFO_REGION_CODE);
        if (!empty($regionStoreCode)) {
            $regionStoreCode = $this->getRegionCodeById($regionStoreCode);
            $address['address']['state'] = $regionStoreCode;
        }
        $address['address']['postalCode'] = $this->getConfig(self::XML_PATH_STORE_INFO_POSTCODE);

        return $address;
    }

    public function getRegionCodeByNameAndCountryId($regionName, $countryId)
    {
        $region = $this->loadRegion(null, null, $regionName, $countryId);
        if ($region) {
            return $region->getCode();
        }
        return '';
    }

    public function getRegionCodeById($regionId)
    {
        $result = null;
        $region = $this->loadRegion($regionId);
        if ($region->getId()) {
            $result = $region->getCode();
        }
        return $result;
    }

    private function canRegionBeLoaded($regionId = null, $regionCode = null, $regionName = null, $countryId = null)
    {
        return $regionId || $countryId && ($regionCode || $regionName);
    }

    public function loadRegion($regionId = null, $regionCode = null, $regionName = null, $countryId = null)
    {
        if (!$this->canRegionBeLoaded($regionId, $regionCode, $regionName, $countryId)) {
            return null;
        }
        $region = $this->regionFactory->create();
        // Load the region by the data provided
        if ($regionId) {
            $region->load($regionId);
        } elseif ($regionCode) {
            $region->loadByCode($regionCode, $countryId);
        } elseif ($regionName) {
            $region->loadByName($regionName, $countryId);
        }
        return $region;
    }

    public function getCountryId($countryName)
    {
        $countryId = '';
        $countryCollection = $this->country->getCollection();
        foreach ($countryCollection as $country) {
            if ($countryName == $country->getName()) {
                $countryId = $country->getCountryId();
                break;
            }
        }
        ($countryId) || $countryId = $countryName;
        $countryCollection = null;
        return $countryId;
    }

    public function getConfig($config_path, $storecode = null)
    {
        return $this->scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storecode);
    }

    public function getIsTaxConfigEnabled($storecode = null)
    {
        return $this->getConfig(self::XML_PATH_TAX_CONFIG_ACTIVE, $storecode);
    }

    public function getIsStoredMethodsEnabled($storecode = null)
    {
        return $this->getConfig(self::XML_PATH_STORED_METHODS_ENABLED, $storecode);
    }
    

    public function getDefaultCountry($storecode = null)
    {
        return $this->getConfig(self::XML_PATH_COUNTRY_CODE_PATH, $storecode);
    }

    public function getZipOptionalCountries()
    {
        return $this->getConfig(self::XML_PATH_OPTIONAL_ZIP_COUNTRIES);
    }

    public function getAllowedCountries()
    {
        return $this->getConfig(self::XML_PATH_ALLOWED_COUNTRIES);
    }

    public function getStateRequiredCountries()
    {
        return $this->getConfig(self::XML_PATH_STATE_REQUIRED_COUNTRIES);
    }

    /**
     * @return mixed|null
     */
    public function getLocale($storecode = null)
    {
        if ($this->getConfig(self::XML_PATH_LOCALE, $storecode) == "zh_Hant_TW") {
            return "zh_TW";
        } elseif ($this->getConfig(self::XML_PATH_LOCALE, $storecode) == "zh_Hans_CN") {
            return "zh_CN";
        } else {
            return $this->getConfig(self::XML_PATH_LOCALE, $storecode);
        }
    }

    /**
     * @return mixed|null
     */
    public function getIsEnabled($storecode = null)
    {
        return $this->getConfig(self::XML_PATH_ENABLE_DRPAY, $storecode);
    }

    /**
     * @param string|null $storeCode
     * @return bool
     */
    public function getIsDisabledRedirect(string $storeCode = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATCH_DISABLE_REDIRECT, $storeCode);
    }

    /**
     * @return mixed|null
     */
    public function getSecretKey($storecode = null)
    {
        $secretKey = $this->getConfig(self::XML_PATH_DRAPI_SECRET_KEY, $storecode);
        return $this->enc->decrypt($secretKey);
    } 

    /**
     * @return mixed|null
     */
    public function getUpstreamId()
    {
        $upstreamId = uniqid();
        return $upstreamId;
    }

    /**
     * @return mixed|null
     */
    public function getUpstreamSessionId()
    {
        $sessionId = $this->sessionManager->getSessionId();
        return $sessionId;
    }

    /**
     * @return mixed|null
     */
    public function getUpstreamApplicationId()
    {
        $magento2Version = $this->productMetadata->getVersion();
        $connectorVersion = self::CONNECTOR_VERSION;
        $magentoEdition = $this->productMetadata->getEdition();
        return 'AdobeMagento' . $magentoEdition . '/' . $connectorVersion . '/' . $magento2Version;
    }

    /**
     * @return mixed|null
     */
    public function getUrl($storecode = null)
    {
        return self::XML_PATH_DRAPI_BASE_URL;
    }

    /**
     * @return mixed|null
     */
    public function getDropInJsUrl($storecode = null)
    {
        return self::XML_PATH_DROPIN_JS_URL;
    }

    /**
     * @return mixed|null
     */
    public function getDropInCssUrl()
    {
        return self::XML_PATH_DROPIN_CSS_URL;
    }

    /**
     * @return mixed|null
     */
    public function getSigningSecret($storecode = null)
    {
        $signingSecret = $this->getConfig(self::XML_PATH_DRAPI_SIGNING_SECRET, $storecode);
        return $this->enc->decrypt($signingSecret);
    }

    /**
     * Get Batch Size limit to fetch sync collection.
     *
     * @param  null $storeCode
     * @return int|null
     */
    public function getBatchSizeLimit($storeCode = null): ?int
    {
        $batchSize = $this->getConfig(self::XML_PATH_CATALOG_SYNC_BUNCH_SIZE, $storeCode);
        return isset($batchSize) ? (int)$batchSize : self::DEFAULT_BATCH_SIZE;
    }

    /**
     * Get Debug mode setting bool value
     *
     * @param  null $storeCode
     * @return bool
     */
    public function isDebugModeEnable($storeCode = null): bool
    {
        return ((bool)$this->getConfig(self::XML_PATH_CATALOG_SYNC_DEBUG_MODE, $storeCode)) && $this->getIsEnabled();
    }

    /**
     * Get TTL for a SKU Group list
     *
     * @return int
     */
    public function getSkuGroupTTL(): int
    {
        return (int)$this->getConfig(self::XML_PATH_SKU_GROUP_TTL);
    }

    /**
     * Get Batch Size limit to fetch sync collection.
     *
     * @param  null $storeCode
     * @return bool
     */
    public function isCatalogSyncEnable($storeId = null): bool
    {
        return ((bool)$this->getConfig(self::XML_PATH_CATALOG_SYNC_ENABLE, $storeId)) && $this->getIsEnabled();
    }
    
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCode();
    }

    /**
     * @return mixed|null
     */
    public function getPublicKey($storecode = null)
    {
        $publicKey = $this->getConfig(self::XML_PATH_DRAPI_PUBLIC_KEY, $storecode);
        return $this->enc->decrypt($publicKey);
    }

    public function round($amount = 0)
    {
        return $this->priceCurrency->round($amount);
    }

    private function doCurl($method, $url, $data = null)
    {
        if (isset($data['metadata']['storecode'])) {
            $storecode = $data['metadata']['storecode'];
            $secret = $this->getSecretKey($storecode);
            if (strpos($url, 'file-links') !== false) {
                unset($data['metadata']);
            }
        } elseif (isset($data['metadata']['storeId'])) {
            $storecode = $this->storeManager->getStore($data['metadata']['storeId'])->getCode(); 
            $secret = $this->getSecretKey($storecode);       
        } else {
            $secret = $this->getSecretKey();
        }
        $upstreamId = $this->getUpstreamId();
        $upstreamSessionId = $this->getUpstreamSessionId();
        $upstreamApplicationId = $this->getUpstreamApplicationId();
        $clientIp = $this->remoteAddress->getRemoteAddress();

        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $secret);
        $this->curl->addHeader("upstream-id", $upstreamId);
        $this->curl->addHeader("upstream-session-id", $upstreamSessionId);
        $this->curl->addHeader("upstream-application-id", $upstreamApplicationId);
        if ($clientIp) {
            $this->curl->addHeader("forwarded", $clientIp);
        }

        $this->logger->info("\n\nDRAPI URL " . $url);

        switch ($method) {
            case 'POST':
                $this->curl->addHeader("Expect", "");
                $this->logger->info("\n\nDRAPI PAYLOAD " . $this->jsonSerializer->serialize($data));
                $this->curl->post($url, $this->jsonSerializer->serialize($data));
                break;
            case 'PUT':
                $this->logger->info("\n\nDRAPI PAYLOAD " . $this->jsonSerializer->serialize($data));
                $this->curl->put($url, $this->jsonSerializer->serialize($data));
                break;
            default:
                $this->curl->get($url);
        }

        $result = $this->curl->getBody();
        $result = $this->jsonSerializer->unserialize($result);
        $statusCode = $this->curl->getStatus();
        $success = true;
        $code = '';
        $parameter = '';
        if (isset($result['errors']) || !in_array($statusCode, ['200', '201', '204'])) {
            $code = isset($result['errors'][0]['code']) ? $result['errors'][0]['code'] : '';
            $result = isset($result['errors'][0]['message']) ? $result['errors'][0]['message'] : '';
            $parameter = isset($result['errors'][0]['parameter']) ? $result['errors'][0]['parameter'] : '';
            $success = false;
        }
        $result = ['success' => $success,
            'statusCode' => $statusCode,
            'code' => $code,
            'parameter' => $parameter,
            'message' => $result];
        $this->logger->info("\n\nDRAPI RESPONSE: " . $this->jsonSerializer->serialize($result));
        return $result;
    }

    public function doCurlPut($service, $data)
    {
        $url = $this->getUrl() . '/' . $service;
        return $this->doCurl('PUT', $url, $data);
    }

    public function doCurlPost($service, $data)
    {
        $url = $this->getUrl() . '/' . $service;
        return $this->doCurl('POST', $url, $data);
    }

    public function doCurlList($service, $search = null)
    {
        $url = $this->getUrl() . '/' . $service;
        if (!empty($search) && is_array($search)) {
            $url .= '?' . http_build_query($search);
        }
        return $this->doCurl('GET', $url);
    }

    public function doCurlGet($service, $id, $data=null)
    {
        $url = $this->getUrl() . '/' . $service . '/' . $id;
        return $this->doCurl('GET', $url, $data);
    }

    public function doCurlDelete($url)
    {
        $this->logger->info("DELETE DRAPI URL " . $url);
        $upstreamId = $this->getUpstreamId();
        $upstreamSessionId = $this->getUpstreamSessionId();
        $upstreamApplicationId = $this->getUpstreamApplicationId();
        $clientIp = $this->remoteAddress->getRemoteAddress();
        $secret = $this->getSecretKey();
        $request = new \Zend\Http\Request();
        $httpHeaders = new \Zend\Http\Headers();
        $client = new \Zend\Http\Client();
        $httpHeaders->addHeaders(
            [
                'Authorization' => 'Bearer ' . $secret,
                'Content-Type' => 'application/json',
                'upstream-id' => $upstreamId,
                'upstream-session-id' => $upstreamSessionId,
                'upstream-application-id' => $upstreamApplicationId,
                'forwarded' => $clientIp ?? ''
            ]
        );

        $request->setHeaders($httpHeaders);
        $request->setMethod(\Zend\Http\Request::METHOD_DELETE);
        $request->setUri($url);
        $client->send($request);
    }
}
