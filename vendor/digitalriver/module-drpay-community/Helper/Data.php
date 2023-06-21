<?php
/**
 * Digitalriver Helper
 */

namespace Digitalriver\DrPay\Helper;

use Magento\Bundle\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Data manager class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const INVOICE_FILE_TYPE = 'INVOICE';
    public const CREDIT_MEMO_FILE_TYPE = 'CREDIT_MEMO';
    private const CUSTOMER_TYPE_INDIVIDUAL = 'individual';
    private const CUSTOMER_TYPE_BUSINESS = 'business';
    private const ERROR_CODE_INVALID_CUSTOMER_TYPE = 'invalid_customer_type';
    private const CUSTOMER_SEGMENT = 'CustomerSegment';
    private const ERROR_CODE_ALREADY_EXISTS = 'already_exists';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Digitalriver\DrPay\Model\ResourceModel\InvoiceCreditMemoLinks\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    private $logger;

    /**
     * @var Drapi
     */
    private $drApi;

    /**
     * @var Comapi
     */
    private $comApi;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Digitalriver\DrPay\Model\AggregatePriceProcessor
     */
    private $aggregatePriceProcessor;

    /**
     * @var \Digitalriver\DrPay\Model\SecondarySourceInfoCollector
     */
    private $secondarySourceInfoCollector;

    /**
     * @var \Digitalriver\DrPay\Model\Customer\CustomerIdCreator
     */
    private $customerIdCreator;

    /**
     * @var \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface
     */
    private $customerIdManagement;

    /**
     * @var \Digitalriver\DrPay\Model\DrConnectorFactory
     */
    private $drFactory;

    /**
     * @var \Digitalriver\DrPay\Model\DrCheckoutDataProviderPool
     */
    private $checkoutDataProviderPool;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $session,
        \Magento\Customer\Model\Session $customerSession,
        \Digitalriver\DrPay\Model\ResourceModel\InvoiceCreditMemoLinks\CollectionFactory $collectionFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Digitalriver\DrPay\Helper\Drapi $drApi,
        \Digitalriver\DrPay\Helper\Comapi $comApi,
        \Digitalriver\DrPay\Helper\Config $config,
        \Digitalriver\DrPay\Model\AggregatePriceProcessor $aggregatePriceProcessor,
        \Digitalriver\DrPay\Model\SecondarySourceInfoCollector $secondarySourceInfoCollector,
        \Digitalriver\DrPay\Model\Customer\CustomerIdCreator $customerIdCreator,
        \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface $customerIdManagement,
        \Digitalriver\DrPay\Model\DrConnectorFactory $drFactory,
        \Digitalriver\DrPay\Model\DrCheckoutDataProviderPool $checkoutDataProviderPool,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->remoteAddress = $remoteAddress;
        $this->serializer = $serializer;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->drApi = $drApi;
        $this->comApi = $comApi;
        $this->config = $config;
        $this->aggregatePriceProcessor = $aggregatePriceProcessor;
        $this->secondarySourceInfoCollector = $secondarySourceInfoCollector;
        $this->customerIdCreator = $customerIdCreator;
        $this->customerIdManagement = $customerIdManagement;
        $this->drFactory = $drFactory;
        $this->checkoutDataProviderPool = $checkoutDataProviderPool;
        $this->jsonSerializer = $jsonSerializer;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
    }

    public function logger($data)
    {
        $this->logger->critical($data);
    }

    /**
     * Creates a new Tax ID
     *
     * @param string $type
     * @param string $value
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createTaxId($type, $value)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        $this->logger->info("Type " . $type);
        $this->logger->info("Value " . $value);

        $createTaxIdResult = $this->validateResponse($this->drApi->createTaxId($type, $value));

        return $createTaxIdResult['message'];
    }

    /**
     * Creates a new Invoice Attribute ID
     *
     * @param string $checkoutId
     * @param string $invoiceAttributeId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoiceAttributeId($checkoutId, $invoiceAttributeId)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        $this->logger->info("Invoice Attrribute ID" . $invoiceAttributeId);

        $createInvoiceAttributeIdResult = $this->validateResponse(
            $this->drApi->createInvoiceAttributeId($checkoutId, $invoiceAttributeId)
        );

        return $createInvoiceAttributeIdResult['message'];
    }

    /**
     * Validates DR Api response
     *
     * It throws an exception if the response success status is false, or response is malformed
     *
     * @param array $createTaxIdResult
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateResponse(array $createTaxIdResult)
    {
        if ($createTaxIdResult &&
            $createTaxIdResult['success'] &&
            $createTaxIdResult['message']) {
            return $createTaxIdResult;
        } elseif ($createTaxIdResult &&
            $createTaxIdResult['success'] === false &&
            $createTaxIdResult['code'] &&
            $createTaxIdResult['message']) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' .
                $createTaxIdResult['code'] . ': ' .
                $createTaxIdResult['message']);

            throw new \Magento\Framework\Exception\LocalizedException(__($createTaxIdResult['message']));
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('Invald DR Api response'));
    }

    /**
     * Validates DR Api response
     *
     * It throws an exception if the response success status is false, or response is malformed
     *
     * @param array $createInvoiceAttributeIdResult
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateInvoiceAttributeResponse(array $createInvoiceAttributeIdResult)
    {
        if ($createInvoiceAttributeIdResult &&
            $createInvoiceAttributeIdResult['success'] &&
            $createInvoiceAttributeIdResult['message']) {
            return $createInvoiceAttributeIdResult;
        } elseif ($createInvoiceAttributeIdResult &&
            $createInvoiceAttributeIdResult['success'] === false &&
            $createInvoiceAttributeIdResult['code'] &&
            $createInvoiceAttributeIdResult['message']) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' .
                $createInvoiceAttributeIdResult['code'] . ': ' .
                $createInvoiceAttributeIdResult['message']);

            throw new \Magento\Framework\Exception\LocalizedException(__($createInvoiceAttributeIdResult['message']));
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('Invald DR Api response'));
    }

    /**
     * Assigns source to checkout
     *
     * @param string $sourceId
     * @param string $checkoutId
     * @return array|false
     */
    public function assignSourceToCheckout($sourceId, $checkoutId)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        try {
            $result = $this->drApi->assignSourceToCheckout($sourceId, $checkoutId);

            if ($result &&
                $result['success'] &&
                $result['message']) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return false;
    }

    public function setSku($skuId, $data)
    {
        /* Required fields:
            1. skuId
            2. countryOfOrigin
            3. eccn
            4. taxCode
            5. name
        */
        /*
        SKU Fulfillment Types
            DR fulfilled physical
                1. sku.fulfill is true
                2. sku.taxCode belong to physical goods
                 manufactuerId, partNumber are still required
                 GC Product.mfrPartNumber = Sku.partNumber
                 GC InventoryLineItem.partNumber = GF Product ID

            DR fulfilled digital
                1. sku.fulfill is true
                2. sku.taxCode belong to non-physical goods

            Client fulfilled physical
                1. sku.fulfill is false
                2. sku.taxCode belong to physical goods
                 Since 2009.1.0, allow Client to create a Client fulfilled physical sku without part number
                 GC Product.mfrPartNumber is null if Sku.partNumber is not presented.
                 GC InventoryLineItem.partNumber would be {SITE_ID}_{Sku.partNumber} or
                    {SITE_ID}_{Sku.id} depends on the presence of sku.partNumber

            Client fulfilled digital
                1. sku.fulfill is false
                2. sku.taxCode belong to non-physical goods
        */
        if (empty($data) || !is_array($data)) {
            return ['success' => false,
                'code' => 'invalid_parameter',
                'message' => 'Missing SKU Data',
                'statusCode' => 400];
        }
        if (empty($skuId)) {
            // check if $data contains the "id" param
            if (isset($data['id'])) {
                $skuId = $data['id'];
                unset($data['id']);
            } else {
                return ['success' => false,
                    'code' => 'invalid_parameter',
                    'message' => 'Missing SKU ID',
                    'statusCode' => 400];
            }
        }

        if (!isset($data['name'])) {
            return ['success' => false,
                'code' => 'invalid_parameter',
                'message' => 'Missing SKU Name',
                'statusCode' => 400];
        }
        if (!isset($data['eccn'])) {
            return ['success' => false,
                'code' => 'invalid_parameter',
                'message' => 'Missing SKU ECCN',
                'statusCode' => 400];
        }
        if (!isset($data['taxCode'])) {
            return ['success' => false,
                'code' => 'invalid_parameter',
                'message' => 'Missing SKU tax code',
                'statusCode' => 400];
        }
        if (!isset($data['countryOfOrigin'])) {
            return ['success' => false,
                'code' => 'invalid_parameter',
                'message' => 'Missing SKU country of origin',
                'statusCode' => 400];
        }
        if(isset($data['skuGroupId']) && $data['skuGroupId'] == 0){
            $data['skuGroupId'] = null;
        }

        return $this->drApi->setSku($skuId, $data);
    }

    public function getSku($skuId)
    {
        if (empty($skuId)) {
            return ['success' => false,
                'code' => 'invalid_parameter',
                'message' => 'Missing SKU ID',
                'statusCode' => 400];
        }
        return $this->drApi->getSku($skuId);
    }

    public function getDrAddress($shippingAddress)
    {
        $return = [];

        if (!empty($shippingAddress->getCity())) {
            $street = $shippingAddress->getStreet();
            $return['address']['line1'] = isset($street[0]) ? $street[0] : '';
            $return['address']['line2'] = isset($street[1]) ? $street[1] : '';
            $return['address']['city'] = $shippingAddress->getCity();
            $return['address']['country'] = $shippingAddress->getCountryId();
            $state = $this->config->getRegionCodeByNameAndCountryId(
                $shippingAddress->getRegion(),
                $shippingAddress->getCountryId()
            );
            $return['address']['state'] = $state ?: $shippingAddress->getRegion();
            $return['address']['postalCode'] = $shippingAddress->getPostcode();

            // The shipping address element in the quote does not contain email address
            $return['phone'] = $shippingAddress->getTelephone();
            $return['name']= trim($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname());
            $return['email'] = $shippingAddress->getEmail();
            $return['organization'] = ($shippingAddress->getCompany()) ? $shippingAddress->getCompany() : '';
        }
        return $return;
    }

    /**
     * @param AddressInterface $address
     * @return array
     */
    private function getPurchaseLocation(AddressInterface $address): array
    {
        $return = [];
        if ($address->getCity() === null) {
            $return['country'] = $address->getCountryId();
            $state = $this->config->getRegionCodeByNameAndCountryId(
                $address->getRegion(),
                $address->getCountryId()
            );
            $return['state'] = $state ?: $address->getRegion();
            $return['postalCode'] = $address->getPostcode();
        }

        // country is required
        if (empty($return['country'])) {
            $return = [];
        } elseif ($return['country'] == 'US' && (empty($return['state']) || empty($return['postalCode']))) {
            // if US, require state and postalCode
            $return = [];
        } elseif ($return['country'] != 'US' && (empty($return['state']) && empty($return['postalCode']))) {
            // if not US, require either state or postalCode
            $return = [];
        } elseif ($address->getAddressType() === 'shipping' &&
            (empty($address->getShippingMethod()) || $address->getCollectShippingRates() === true)) {
            // if this is a shipping address and a method has not been chosen or we're collecting rates
            $return = [];
        }

        return $return;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function setCustomer($email)
    {
        try {
            $currentStoreId = $this->storeManager->getStore()->getId();
            $drCustomerId = $this->customerIdManagement->getSessionDigitalRiverCustomerId();

            if ($this->customerSession->isLoggedIn()) {
                $customer = $this->customerSession->getCustomerData();
                if (!$drCustomerId) {
                    $this->logger->info("FUNCTION " . __FUNCTION__);
                    $drCustomerCreateId = $this->customerIdCreator->getCustomerId($customer);
                    $data['id'] = $drCustomerCreateId;
                    // When implementing US TEMs, this will conditionally be 'business'
                    $data['type'] = self::CUSTOMER_TYPE_INDIVIDUAL;
                    $data['email'] = $email;
                    $data['locale'] = $this->config->getLocale();
                    $data['metadata']['CustomerSegment'] = self::CUSTOMER_SEGMENT;
                    $result = $this->drApi->setCustomer($data);
                    $this->customerSession->setCurrentCustomerStoreId($currentStoreId);
                    if (($result['success'] == false && $result['code'] == self::ERROR_CODE_ALREADY_EXISTS) ||
                        $result['success']) {
                        $this->customerIdManagement->persistSessionDigitalRiverCustomerId($drCustomerCreateId);
                        $drCustomerId = $drCustomerCreateId;
                    }
                } elseif (!$this->customerIdManagement->hasDigitalRiverCustomerId($customer)) {
                    $this->customerIdManagement->persistSessionDigitalRiverCustomerId($drCustomerId);
                }
            }

            return $drCustomerId;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * @param  mixed  $sourceId
     * @return mixed|null
     */
    public function setCustomerSource($sourceId)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->setCustomerSource($sourceId);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * @param $checkoutId
     * @return mixed|void|null
     */
    public function getCheckout($checkoutId)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->getCheckout($checkoutId);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    public function getCheckoutItemQuantitiesShippingAmount($checkoutId)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $drCheckout = $this->getCheckout($checkoutId);
            $checkoutData['items'] = [];
            if (isset($drCheckout['message']['items'])) {
                foreach ($drCheckout['message']['items'] as $item) {
                    $checkoutData['items'][$item['metadata']['magento_quote_item_id']] = $item['quantity'];
                }
            }
            $checkoutData['shipping'] = $drCheckout['message']['shippingChoice'] ?? [];
            return $checkoutData;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * Get source info relevant for checkout
     *
     * @param string $sourceId
     * @return array
     */
    public function getCheckoutSourceInfo(string $sourceId) : array
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        $this->logger->info("SOURCE DATA " . json_encode($sourceId));

        try {
            $result = $this->getSourceDetails($sourceId);

            $checkoutSourceInfo = [];
            $checkoutSourceInfo['payment'] = [];
            $checkoutSourceInfo['message'] = [];

            if ($result['success']) {
                if ($result['message']) {
                    $checkoutSourceInfo['message']['owner'] = $result['message']['owner'];
                    $checkoutSourceInfo['payment']['type'] = $result['message']['type'];
                    if ($result['message']['type'] === 'creditCard') {
                        $creditCard = $result['message']['creditCard'];
                        if ($creditCard) {
                            $checkoutSourceInfo['payment']['brand'] = $creditCard['brand'];
                            $checkoutSourceInfo['payment']['lastFourDigits'] = $creditCard['lastFourDigits'];
                        }
                    }
                }
            }

            return $checkoutSourceInfo;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * Sets a secundary source
     *
     * @param string $checkoutId
     * @param string $paymentSessionId
     * @param \Magento\Quote\Model\Quote\Address $shippingAddress
     * @param float $amount
     * @return array|false
     */
    protected function setSecondarySource(
        $checkoutId,
        $paymentSessionId,
        $shippingAddress,
        $amount
    ) {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        try {
            $secondarySource = [];
            $secondarySource['paymentSessionId'] = $paymentSessionId;
            $secondarySource['amount'] = $amount;
            $secondarySource['type'] = 'customerCredit';
            $secondarySource['customerCredit'] = new \stdClass();

            $result = $this->setSource($secondarySource);

            if ($result &&
                $result['success'] &&
                $result['message']
            ) {
                $sourceId = $result['message']['id'];
                if ($sourceId) {
                    return $this->assignSourceToCheckout($sourceId, $checkoutId);
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return false;
    }

    public function getSetCheckoutItemData($quote)
    {
        $tax_inclusive = $this->config->isTaxInclusive();

        $quoteItems = $quote->getAllItems();
        if (empty($quoteItems)) {
            return false;
        }

        $shipFrom = $this->config->getDrStoreInfo();

        $items = [];

        foreach ($quoteItems as $item) {
            if ($item->getProductType() == Configurable::TYPE_CODE || $item->getProductType() == Type::TYPE_CODE) {
                continue;
            }
            if ($item->getParentItemId()) {
                if ($item->getParentItem()->getProductType() == Configurable::TYPE_CODE) {
                    $item = $item->getParentItem();
                }
            }
            $lineItem =  [];
            $lineItem['quantity'] = $item->getQty();
            if ($item->getParentItemId()) {
                if ($item->getParentItem()->getProductType() == Type::TYPE_CODE) {
                    $lineItem['quantity'] = $item->getQty() * $item->getParentItem()->getQty();
                }
            }
            $sku = $item->getSku();
            $aggregatePrice = ($this->aggregatePriceProcessor->getAggregatePrice($item) * $lineItem['quantity'])
                - $item->getDiscountAmount();

            if ($tax_inclusive) {
                $lineItem['metadata']['productPriceSubTotalInclTax'] = $aggregatePrice;
            } else {
                $lineItem['metadata']['productPriceSubTotal'] = $aggregatePrice;
            }

            $lineItem['metadata']['magento_quote_item_id'] = $item->getId();
            $discountAmount = $item->getDiscountAmount() ?? 0;
            $lineItem['metadata']['productDiscount'] = round($discountAmount, 2);
            $lineItem['skuId'] = $sku;
            $lineItem['aggregatePrice'] = $aggregatePrice;
            if ($item->getProduct()->getIsVirtual() === false) {
                $lineItem['shipFrom'] = $shipFrom;
            }

            if ($item->getParentItemId()) {
                $lineItem['metadata']['parentExternalReferenceId'] = $item->getParentItem()->getSku();
            }

            $items[] = $lineItem;
        }

        // allow custom integrations to manipulate shipFrom for items
        $quote->setDrDefaultShipFrom($shipFrom);
        $quote->setDrItemsPayload($items);

        $this->eventManager->dispatch('dr_checkout_item_ship_from', ['quote' => $quote]);

        return $quote->getDrItemsPayload();
    }

    private function setCheckoutValidationError($errorMessage)
    {
        $this->logger->info("Failed Checkout - " . $errorMessage);
        $this->session->setDrQuoteError(true);
        return false;
    }

    /**
     * Helper function that sets up a checkout session with DR
     *
     * @param $quote CartInterface
     * @return array|bool
     *
     */
    public function setCheckout($quote)
    {

        // add check if the connector is enabled
        if (!$this->config->getIsEnabled()) {
            return;
        }

        $this->logger->info("FUNCTION " . __FUNCTION__);
        try {
            $tax_inclusive = $this->config->isTaxInclusive();
            $data = [];

            $data['email'] = $this->customerSession->isLoggedIn() ?
                $this->customerSession->getCustomer()->getEmail() : $this->session->getGuestCustomerEmail();

            $data['metadata']['QuoteID'] = ($quote->getId()) ? $quote->getId() : 0;
            $data['taxInclusive'] = $tax_inclusive ? true : false;
            $data['currency'] = $this->config->getCurrencyCode();

            // set the source ID = '' so that it does not default to the authenticated shopper's default source ID
            $data['sourceId'] = '';

            //apply the browser IP to the payload
            $ip = $this->remoteAddress->getRemoteAddress();
            $data['browserIp'] = $ip;

            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();

            $drBillingAddress = $this->getDrAddress($billingAddress);
            // include billing address if valid
            if (!empty($drBillingAddress)) {
                $data['billTo'] = $drBillingAddress;
            }

            $drShippingAddress = $this->getDrAddress($shippingAddress);
            $purchaseLocationAddress = $quote->getIsVirtual() ? $billingAddress : $shippingAddress;
            $purchaseLocation = $this->getPurchaseLocation($purchaseLocationAddress);
            if (!empty($purchaseLocation)) {
                $data['purchaseLocation'] = $purchaseLocation;
            }
            // include shipping address if valid
            // only physical quotes can have a shipTo
            if (!empty($drShippingAddress) && !$quote->getIsVirtual()) {
                $data['shipTo'] = $drShippingAddress;
            }

            // physical quotes require a valid shipTo
            // virtual quotes require a valid billTo
            if (($quote->getIsVirtual() && (!isset($data['billTo']) && !isset($data['purchaseLocation']))) ||
                (!$quote->getIsVirtual() && (!isset($data['shipTo']) && !isset($data['purchaseLocation'])))) {
                return $this->setCheckoutValidationError('billTo, shipTo or purchaseLocation missing');
            }

            // if physical, ensure line1, city, and country are always set
            // ensure zip and state are set based on Magento settings
            if (!$quote->getIsVirtual() && !isset($data['purchaseLocation'])) {
                if (empty($data['shipTo']['address']['line1'])) {
                    return $this->setCheckoutValidationError('line1 missing');
                }

                if (empty($data['shipTo']['address']['city'])) {
                    return $this->setCheckoutValidationError('city missing');
                }

                if (empty($data['shipTo']['address']['country'])) {
                    return $this->setCheckoutValidationError('country missing');
                }

                $zipCountries = explode(',', $this->config->getZipOptionalCountries());
                if (!in_array($data['shipTo']['address']['country'], $zipCountries) &&
                    empty($data['shipTo']['address']['postalCode'])) {
                    return $this->setCheckoutValidationError('postalCode missing');
                }

                $stateCountries = explode(',', $this->config->getStateRequiredCountries());
                if (in_array($data['shipTo']['address']['country'], $stateCountries) &&
                    empty($data['shipTo']['address']['state'])) {
                    return $this->setCheckoutValidationError('state missing');
                }
            }

            // attempt to create the customer
            $drCustomerId = $this->setCustomer($shippingAddress->getEmail());
            if ($drCustomerId) {
                $data['customerId'] = (string) $drCustomerId;
            }

            $data['locale'] = $this->config->getLocale();

            $itemData = $this->getSetCheckoutItemData($quote);
            if (!$itemData) {
                return $this->setCheckoutValidationError('item data missing');
            }

            $data['items'] = $itemData;

            $data['metadata']['shippingDiscount'] = 0;

            if ($this->isPurchaseOrderPayment($quote)) {
                $data['metadata']['po_number'] = $quote->getPayment()->getPoNumber();
            }

            if (!$quote->getIsVirtual()) {
                $shippingAmount = $quote->getShippingAddress()->getShippingAmountForDiscount();
                if (empty($shippingAmount)) {
                    $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
                }

                $data['metadata']['shippingAmount'] = $this->config->round($shippingAmount);
                $data['metadata']['shippingDiscount'] = $this->config->round(
                    $quote->getShippingAddress()->getShippingDiscountAmount()
                );

                if ($shippingAmount > 0 && $quote->getShippingAddress()->getShippingDiscountAmount() > 0) {
                    $shippingAmount = $shippingAmount - $quote->getShippingAddress()->getShippingDiscountAmount();
                }

                $shippingChoice['amount'] = $this->config->round($shippingAmount);
                $shippingChoice['description'] = $quote->getShippingAddress()->getShippingDescription() ?? "";
                $shippingChoice['serviceLevel'] = 'SG';
                $data['shippingChoice'] = $shippingChoice;
            }

            if ($this->getDrTaxIds($quote) === false) {
                $data['taxIdentifiers'] = [];
            } else {
                $data['taxIdentifiers'] = $this->getDrTaxIds($quote);
            }

            if ($this->getDrInvoiceAttribute($quote) === false) {
                $data['invoiceAttributeId'] = null;
            } else {
                $data['invoiceAttributeId'] = $this->getDrInvoiceAttribute($quote);
            }

            $data['customerType'] = 'individual';
            $customerType = $quote->getDrCustomerType();
            if ($customerType) {
                $data['customerType'] = $customerType;
            }

            $data = array_merge_recursive($data, $this->checkoutDataProviderPool->execute($quote));
            /*******************************************************/
            /*    PROCESS THE PAYLOAD THRU DR API FLEET */
            /*******************************************************/
            $result = $this->drApi->setCheckout($data);
            if ($result['success'] == false) {
                $this->session->setDrQuoteError(true);
                $this->session->unsSessionCheckSum();
                $this->session->unsDrCheckoutBillingChecksum();
                $this->session->unsDrCheckoutItemChecksum();

                $quote->setBaseDrDutyFee(0);
                $quote->setBaseDrIorTax(0);
                $quote->setDrDutyFee(0);
                $quote->setDrIorTax(0);
                $quote->setIsDrIorSet(false);

                $quote->setDrInvoiceAttributes(null);
                $quote->setDrTaxIdentifiers(null);
                $quote->setDrCustomerType(null);
                $quote->save();

                return false;
            }

            $this->session->setDrQuoteError(false);
            $this->session->setDrCheckoutId($result['id']);

            $this->logger->info("CHECKOUT " . $this->jsonSerializer->serialize($result));

            $shippingTax = $result['shippingTax'];
            $productTax = $result['productTax'];
            $productTotalExclTax = $result['productTotalExclTax'];
            $productTotalInclTax = $result['productTotalExclTax'] + $productTax;
            $shippingTotalExclTax = $result['shippingTotalExclTax'];
            $shippingTotalInclTax = $result['shippingTotalExclTax'] + $result['shippingTax'];

            $quote->setSubtotal($productTotalExclTax + $result['subTotalDiscount']);
            $quote->setBaseSubtotal($this->config->convertToBaseCurrency($productTotalExclTax
                + $result['subTotalDiscount']));

            $quote->setSubtotalWithDiscount($productTotalExclTax);
            $quote->setBaseSubtotalWithDiscount($this->config->convertToBaseCurrency($productTotalExclTax));

            $quote->setShippingAmount($shippingTotalExclTax);
            $quote->setBaseShippingAmount($this->config->convertToBaseCurrency($shippingTotalExclTax));

            $orderTotal = $result['orderTotal'];
            $quote->setCheckoutGrandTotal($quote->getGrandTotal());
            $quote->setGrandTotal($orderTotal);
            $quote->setBaseGrandTotal($this->config->convertToBaseCurrency($orderTotal));

            $this->session->setDrShippingTax($shippingTax);
            $this->session->setDrShippingAndHandling($shippingTotalInclTax);
            $this->session->setDrShippingAndHandlingExcl($shippingTotalExclTax);
            $this->session->setDrOrderTotal($orderTotal);
            $this->session->setDrProductTax($productTax);
            $this->session->setDrProductTotal($productTotalInclTax);
            $this->session->setDrProductTotalExcl($productTotalExclTax);

            $drTax = $result['orderTax'];
            $quote->setTaxAmount($drTax);
            $quote->setBaseTaxAmount($this->config->convertToBaseCurrency($drTax));
            $quote->setDrTax($drTax);
            $this->session->setDrTax($drTax);

            if (isset($result['importerOfRecordTax']) && $result['importerOfRecordTax']) {
                $quote->setBaseDrDutyFee($this->config->convertToBaseCurrency($result['totalDuty']));
                $quote->setBaseDrIorTax($this->config->convertToBaseCurrency($result['totalImporterTax']));
                $quote->setDrDutyFee($result['totalDuty']);
                $quote->setDrIorTax($result['totalImporterTax']);
                $quote->setIsDrIorSet(true);
            } else {
                $quote->setBaseDrDutyFee(0);
                $quote->setBaseDrIorTax(0);
                $quote->setDrDutyFee(0);
                $quote->setDrIorTax(0);
                $quote->setIsDrIorSet(false);
            }
            $this->session->setDrPaymentSessionId($result['paymentSessionId']);
            $this->session->setDrSellingEntity($result['sellingEntity']);

            if (isset($result['setSecondary']) && $result['setSecondary'] === true) {
                $secondaryInfo = $this->secondarySourceInfoCollector->collect($result, $quote);
                if (isset($secondaryInfo['amount']) && $secondaryInfo['amount'] > 0) {
                    $this->setSecondarySource(
                        $secondaryInfo['checkoutId'],
                        $secondaryInfo['paymentSessionId'],
                        $secondaryInfo['shippingAddress'],
                        $secondaryInfo['amount']
                    );
                }
            }
        } catch (\Exception $e) {
            $this->session->setDrQuoteError(true);
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    public function getCustomer($customerId)
    {
        $result = $this->drApi->getCustomer($customerId);
        return $result;
    }

    /**
     * @return array|null
     */
    public function getSavedSources($customerId)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $cardData = [];
            $result = $this->getCustomer($customerId);
            if ($result['success']) {
                $customer = $result['message'];
                if (isset($customer['sources'])) {
                    foreach ($customer['sources'] as $source) {
                        if (isset($source['type']) && $source['reusable'] === true && $source['type'] == 'creditCard') {
                            $content = __("Credit Card: ending with ") .
                                $source['creditCard']['lastFourDigits'];
                            $struct = [];
                            $struct['content'] = $content;
                            $struct['sourceId'] = $source['id'];
                            $struct['sourceClientSecret'] = $source['clientSecret'];
                            $cardData[$source['id']] = $struct;
                        }
                    }
                    $result['message'] = $cardData;
                } else {
                    $result['success'] = false;
                }
            } else {
                $result['success'] = false;
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Creates DR API source
     *
     * @param array $sourceInfo
     * @return mixed
     */
    public function setSource($sourceInfo)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        try {
            return $this->drApi->setSource($sourceInfo);
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    public function setCheckoutUpdate($checkoutId, $data)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->setCheckoutUpdate($checkoutId, $data);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return [];
    }

    /**
     * @param  mixed $accessToken
     * @return mixed|null
     */
    public function setOrder($checkoutId, $upstreamId)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->setOrder($checkoutId, $upstreamId);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * @param $drOrderId
     * @return mixed|void|null
     */
    public function getDrOrder($drOrderId)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->getDrOrder($drOrderId);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * Refresh order by drOrderId, only for TEST needs.
     *
     * @param $orderId
     * @param $data
     * @return array
     */
    public function refreshOrder($orderId, $data = [])
    {
        $result = [];
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->refreshOrder($orderId, $data);
        } catch (\Exception $e) {
            $this->logger->error(
                'Error: ' . __FUNCTION__ . ': ' . $e->getMessage(),
                [
                    'stack_trace' => $e->getTraceAsString()
                ]
            );
        }

        return $result;
    }

    public function getSourceDetails($sourceId)
    {
        $result=[];
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $result = $this->drApi->getSourceDetails($sourceId);
            if (!$result['success']) {
                throw new \Magento\Framework\Exception\LocalizedException(__($result['message']));
            }
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * This is a COM API specific call
     *
     * @object $order
     * @return type
     */
    public function setOrderStateComplete($order)
    {
        try {
            if ($order->getDrOrderId()) {
                $this->logger->info("FUNCTION " . __FUNCTION__);
                $drModel = $this->drFactory->create()->load($order->getDrOrderId(), 'requisition_id');
                if (!$drModel->getId()) {
                    return false;
                }
                if ($drModel->getPostStatus() == 1) {
                    return false;
                }
                $drApiType = $order->getDrApiType();
                if ($drApiType != 'drapi') {
                    $result = $this->comApi->setOrderStateComplete($order);
                    if ($result['success']) {
                        $drModel = $this->drFactory->create()->load($order->getDrOrderId(), 'requisition_id');
                        $drModel->setPostStatus(1);
                        $drModel->save();
                    }
                    return $result['success'];
                }
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return false;
    }

    /**
     *
     * @return type
     */
    public function setOrderCancellation($order)
    {
        try {
            $this->logger->info("FUNCTION " . __FUNCTION__);
            $this->logger->info($this->jsonSerializer->serialize($order));

            $result = $this->drApi->setOrderCancellation($order);
            $this->session->unsDrCheckoutId();
            $this->session->unsDrLockedInCheckoutId();
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @return float|bool
     * @throws LocalizedException
     */
    public function setRefundRequest(CreditmemoInterface $creditmemo)
    {
        $result = true;
        try {
            $order = $creditmemo->getOrder();
            if ($order->getDrOrderId()) {
                $drApiType = $order->getDrApiType();
                if ($drApiType == 'drapi') {
                    $result = $this->drApi->setRefundRequest($creditmemo);
                } else {
                    $result = $this->comApi->setRefundRequest($creditmemo);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }

        if ($result === null || $result === false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed to save credit memo'));
        }
        return $result;
    }

    /**
     * Function to validate Quote for any errors, As in some cases Magento encounters an exception.
     * To avoid this, Quote is validated before proceeding for order processing
     *
     * @param  object $quote
     * @return bool $isValidQuote
     **/
    public function isQuoteValid(\Magento\Quote\Model\Quote $quote)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        $isValidQuote = false;
        try {
            $errors         = $quote->getErrors();
            $isValidQuote   = (empty($errors)) ? true : false;
        } catch (\Magento\Framework\Exception\LocalizedException $le) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $le->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return $isValidQuote;
    }

    /**
     * Function to fetch Billing & Shipping address from DR order creation response
     *
     * @param array $drResponse
     *
     * @return array $returnAddress
     */
    public function getBillingAddressFromSource($source)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        $this->logger->info("SOURCE DATA " . $this->jsonSerializer->serialize($source));
        $returnAddress = false;
        if ($source && !empty($source['message'])
            && !empty($source['message']['owner'])
            && !empty($source['message']['owner']['address']['country'])
            && !empty($source['message']['owner']['address']['line1'])
            && !empty($source['message']['owner']['address']['city'])
            && !empty($source['message']['owner']['firstName'])
            && !empty($source['message']['owner']['lastName'])
            && !empty($source['message']['owner']['phoneNumber'])) {

            $sourceInfo = $source['message']['owner'];
            $sourceInfo['address']['country'] = $this->config->getCountryId($sourceInfo['address']['country']);
            $returnAddress = [
                'firstname'     => $sourceInfo['firstName'],
                'lastname'      => $sourceInfo['lastName'],
                'street'        => $sourceInfo['address']['line1'],
                'city'          => $sourceInfo['address']['city'],
                'postcode'      => $sourceInfo['address']['postalCode'] ?? '',
                'country_id'    => $sourceInfo['address']['country'],
                'telephone'   => $sourceInfo['phoneNumber']           
            ];
            if (isset($sourceInfo['address']['line2']) && !empty($sourceInfo['address']['line2'])) {
                $returnAddress['street'] .= "\n" . $sourceInfo['address']['line2'];
            }
            if (isset($sourceInfo['address']['state'])) {
                // test if the state is a code
                $region = $this->config->loadRegion(
                    null,
                    $sourceInfo['address']['state'],
                    null,
                    $sourceInfo['address']['country']
                );
                if (!$region || !$region->getCode()) {
                    // try as the state name
                    $region = $this->config->loadRegion(
                        null,
                        null,
                        $sourceInfo['address']['state'],
                        $sourceInfo['address']['country']
                    );
                }
                $returnAddress['region'] = ($region && $region->getCode()) ?
                    $region->getCode() : $sourceInfo['address']['state'];
                $returnAddress['region_id'] = $region->getRegionId();
            } else {
                $returnAddress['region'] = null;
                $returnAddress['region_id'] = null;
            }
        }
        $this->logger->info("RETURN ADDRESS " . $this->jsonSerializer->serialize($returnAddress));
        return $returnAddress;
    }

    /**
     * Returns a timestamp 10 years later than the current one
     *
     * @return string
     */
    private function getFileLinkExpiresTime(): string
    {
        $date = $this->dateTime->gmtDate('Y-m-d H:i:s');
        $expiryYear = (int)substr($date, 0, 4) + 10;
        return '' . $expiryYear . substr($date, 4, 6) . 'T' . substr($date, 11, 8) . 'Z';
    }

    /**
     * Makes an API request to DR to generate a URL to access a file
     * The fileID for which the URL should be created is provided
     * The expiry time for the link is set to 10 years as default
     *
     * @param string $fileId
     * @return array
     */
    public function setFileLink(string $fileId,$storecode): array
    {
        $response = [];
        try {
            $this->logger->info('Function: ' . __FUNCTION__);
            $request = [
                "fileId" => $fileId,
                "expiresTime" => $this->getFileLinkExpiresTime(),
                "metadata" => [
                    "storecode" => $storecode
                ]
            ];
            $response = $this->drApi->setFileLink($request);

            //DIRI-131 Adds file-link creation time to display most recent link at the top
            $response['createdAt'] = $this->dateTime->gmtDate('Y-m-d H:i:s');
        } catch (\Exception $exception) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $exception->getMessage());
        }
        return $response;
    }

    /**
     * Returns the files of specific type for an order
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $type
     * @return array
     */
    public function getAllFiles(\Magento\Sales\Model\Order $order, string $type): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('sales_order_id', ['eq' => $order->getId()])
            ->addFieldToFilter('dr_file_type', ['eq' => $type]);
        return $collection->getItems();
    }

    public function getDrFile(string $fileId): \Magento\Framework\DataObject
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('dr_file_id', ['eq' => $fileId]);
        return $collection->getFirstItem();
    }

    /**
     * Function to send EFN request to DR when Invoice/Shipment created from Magento Admin
     * Only Invoice/Shipment Success cases are sent
     *
     * @param array  $lineItems
     * @param object $order
     *
     * @return array $result
     */
    public function setFulfillmentRequest($lineItems, $order)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        try {
            if ($order->getDrOrderId()) {
                $drModel = $this->drFactory->create()->load($order->getDrOrderId(), 'requisition_id');
                if (!$drModel->getId() || $drModel->getPostStatus() == 1) {
                    return;
                }

                $drApiType = $order->getDrApiType();
                if ($drApiType == 'drapi') {

                    // get the fulfillments already made to DR
                    $result = $this->drApi->setFulfillmentRequest($lineItems, $order);
                } else {
                    $result = $this->comApi->setFulfillmentRequest($lineItems, $order);
                }

                if ($result['success']) {
                    // Post Status updated only if entire order items are fulfilled
                    $canInvoice = $order->canInvoice(); // returns true for pending items
                    $canShip = $order->canShip();  // returns true for pending items
                    // Return true if both invoice and shipment are false, i.e. No items to fulfill
                    if (empty($canInvoice) && empty($canShip)) {
                        // if all the quantites are satisfied then mark as 1
                        $drModel = $this->drFactory->create()->load($order->getDrOrderId(), 'requisition_id');
                        $drModel->setPostStatus(1);
                        $drModel->save();
                        $comment = 'Order fulfilled';
                    } else {
                        $comment = 'This order has been partially fulfilled. One or more items remain to be shipped';
                    }
                    $order->addStatusToHistory($order->getStatus(), __($comment));
                }
            } else {
                $this->logger->error('Error: ' . __FUNCTION__ . ': Empty DR Order Id');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        } // end: try

        return $result;
    }

    /**
     * Function to send EFN request to DR when @OrderItem is cancelled from Magento Admin
     *
     * @param array  $lineItems
     * @param object $order
     *
     * @return array $result
     */
    public function setFulfillmentCancellation($lineItems, $order)
    {
        $this->logger->info("FUNCTION " . __FUNCTION__);
        try {
            if ($order->getDrOrderId()) {
                $drModel = $this->drFactory->create()->load($order->getDrOrderId(), 'requisition_id');

                if (!$drModel->getId() || $drModel->getPostStatus() == 1) {
                    return;
                }

                $drApiType = $order->getDrApiType();
                if ($drApiType == 'drapi') {
                    $result = $this->drApi->setFulfillmentCancellation($lineItems, $order);
                } else {
                    $result = $this->comApi->setFulfillmentCancellation($lineItems, $order);
                }
                $this->logger->info($this->jsonSerializer->serialize($result));
                // Status Update: Existing code used according to review changes
                if ($result['success']) {
                    $comment = 'Push notification: Order cancellation';
                    $order->addStatusToHistory($order->getStatus(), __($comment));
                }
                return ($result['success']);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . ': ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Gets Dr Tax Ids
     * @param $quote
     * @return array|false|mixed
     */
    protected function getDrTaxIds($quote)
    {

        if (!$quote->getDrTaxIdentifiers()) {
            return false;
        }

        $drTaxIds = $this->jsonSerializer->unserialize($quote->getDrTaxIdentifiers());
        if (empty($drTaxIds) || !is_array($drTaxIds)) {
            return false;
        }

        return $drTaxIds;
    }

    /**
     * Gets Dr Invoice Attribute
     * @param $quote
     * @return array|false|mixed
     */
    protected function getDrInvoiceAttribute($quote)
    {
        if (!$quote->getDrInvoiceAttribute()) {
            return false;
        }

        $drInvoiceAttribute = $this->jsonSerializer->unserialize($quote->getDrInvoiceAttribute());
        if (empty($drInvoiceAttribute) || !is_array($drInvoiceAttribute)) {
            return false;
        }

        // currently stored as an array so get the first value
        $drInvoiceAttribute = current($drInvoiceAttribute);
        if (!isset($drInvoiceAttribute['invoiceAttributeId'])) {
            return false;
        }

        return $drInvoiceAttribute['invoiceAttributeId'];
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    private function isPurchaseOrderPayment(\Magento\Quote\Model\Quote $quote): bool
    {
        return $quote->getPayment()->getMethod() == Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE;
    }

    public function isValidCountry($countryAbbreviation)
    {
        $allowedCountries = explode(',', $this->config->getAllowedCountries());
        if (!empty($countryAbbreviation) && in_array($countryAbbreviation, $allowedCountries)) {
            return true;
        }
        return false;
    }
}
