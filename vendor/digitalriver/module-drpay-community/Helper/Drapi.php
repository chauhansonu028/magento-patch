<?php
/**
 * DrApi Helper
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Helper;

use Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface;
use Digitalriver\DrPay\Api\RefundRegistryInterface;
use Digitalriver\DrPay\Api\Webhook\SetOfflineRefundTokenInterface;
use Digitalriver\DrPay\Logger\Logger;
use Digitalriver\DrPay\Model\Customer\CustomerIdCreator;
use Digitalriver\DrPay\Model\DrConnectorFactory;
use Digitalriver\DrPay\Model\FileSend;
use Digitalriver\DrPay\Setup\Patch\Data\AddDrCustomerId;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * DR API helper library
 */
class Drapi extends AbstractHelper
{
    public const REFUND_REASON_SATISFACTION = 'CUSTOMER_SATISFACTION_ISSUE';
    public const REFUND_REASON_REQUESTED = 'REQUESTED_BY_CUSTOMER';
    private const MAX_REFUND_REASON_LENGTH = 64;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var DrConnectorFactory
     */
    private $drFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerIdCreator
     */
    private $creator;

    /**
     * @var FileSend
     */
    private $fileSend;

    /**
     * @var DigitalRiverCustomerIdManagementInterface
     */
    private $customerIdManagement;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var RefundRegistryInterface
     */
    private $refundRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        Session $session,
        DrConnectorFactory $drFactory,
        Logger $logger,
        Config $config,
        EncoderInterface $urlEncoder,
        CustomerRepository $customerRepository,
        RefundRegistryInterface $refundRegistry,
        FileSend $fileSend,
        CustomerIdCreator $creator,
        DigitalRiverCustomerIdManagementInterface $customerIdManagement,
        Json $jsonSerializer,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);

        $this->fileSend = $fileSend;
        $this->customerRepository = $customerRepository;
        $this->session = $session;
        $this->drFactory = $drFactory;
        $this->_logger = $logger;
        $this->config = $config;
        $this->urlEncoder = $urlEncoder;
        $this->creator = $creator;
        $this->customerIdManagement = $customerIdManagement;
        $this->jsonSerializer = $jsonSerializer;
        $this->refundRegistry = $refundRegistry;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    public function setSku($skuId, $data)
    {
        return $this->config->doCurlPut('skus/' . urlencode($skuId), $data);
    }

    public function getSku($skuId)
    {
        return $this->config->doCurlGet('skus', urlencode($skuId));
    }

    /**
     * Get source details
     *
     * @param  string $sourceId
     * @return array $result
     */
    public function getSourceDetails($sourceId)
    {
        return $this->config->doCurlGet('/payments/sources', $sourceId);
    }

    /**
     * Create customer
     *
     * @param  array $data
     * @return array $result
     */
    public function setCustomer($data)
    {
        return $this->config->doCurlPost('customers', $data);
    }

    /**
     * Create customer
     *
     * @param $customerId
     * @param array $data
     * @return array
     */
    public function updateCustomer($customerId, $data)
    {
        return $this->config->doCurlPost("customers/{$customerId}", $data);
    }

    /**
     * Determine if the checkout data has changed and needs to be sent to DR
     * If a secondary source has changed at all, the cache must be cleared to force a new checkout to be created
     * @param $data
     * @return false|mixed
     */
    private function getDrCheckoutCache($data)
    {
        $paymentMethodChanged = false;
        if (array_key_exists('po_number', $data['metadata'])) {
            if (!$this->session->getPurchaseOrderSelected()) {
                $paymentMethodChanged = true;
            }
            $this->session->setPurchaseOrderSelected(true);
        } else {
            if ($this->session->getPurchaseOrderSelected()) {
                $paymentMethodChanged = true;
            }
            $this->session->setPurchaseOrderSelected(false);
        }

        // determine if the po, gift card or store balances changed. if so, we need a new checkout
        $previousGiftCardChecksum = $this->session->getGiftCardChecksum();
        $previousStoreCreditAmount = $this->session->getStoreCreditAmount();
        $currentGiftCardChecksum = isset($data['quoteGiftCards']) ?
            sha1($this->jsonSerializer->serialize($data['quoteGiftCards'])) : null;
        $currentStoreCreditAmount = $data['storeCreditUsed'] ?? 0.0;

        if ($previousGiftCardChecksum != $currentGiftCardChecksum ||
            $previousStoreCreditAmount != $currentStoreCreditAmount ||
            $paymentMethodChanged) {
            $this->session->unsSessionCheckSum();
            $this->session->unsDrCheckoutBillingChecksum();
            $this->session->unsDrCheckoutItemChecksum();
        }

        $this->session->setGiftCardChecksum($currentGiftCardChecksum);
        $this->session->setStoreCreditAmount($currentStoreCreditAmount);

        // if the billing address changes and there is a secondary source created, we need a new checkout
        if (isset($data['billTo']) && !empty($data['billTo'])) {
            $currentBillingChecksum = sha1($this->jsonSerializer->serialize($data['billTo']));
            $previousBillingChecksum = $this->session->getDrCheckoutBillingChecksum();
            if (($currentBillingChecksum != $previousBillingChecksum) &&
                ($this->session->getPurchaseOrderSelected() ||
                    (isset($data['quoteGiftCards']) && !empty($data['quoteGiftCards'])) ||
                    $currentStoreCreditAmount > 0)) {
                $this->session->unsSessionCheckSum();
                $this->session->unsDrCheckoutBillingChecksum();
                $this->session->unsDrCheckoutItemChecksum();
            }

            $this->session->setDrCheckoutBillingChecksum($currentBillingChecksum);
        }

        $checksum = sha1($this->jsonSerializer->serialize($data));
        $existingChecksum = $this->session->getSessionCheckSum();
        if (!empty($existingChecksum) && $checksum == $existingChecksum) {
            $drResult = $this->session->getDrResult();
            if ($drResult) {
                $drResult = $this->jsonSerializer->unserialize($drResult);
                $drResult['setSecondary'] = false; // don't set secondary on cached responses
                return $drResult;
            }
        }

        $this->session->setSessionCheckSum($checksum);
        return false;
    }

    /**
     * Create checkout
     *
     * @param  array $data
     * @return array $response
     */
    public function setCheckout($data)
    {
        $response = [];
        $response['setSecondary'] = true;

        $checkoutCache = $this->getDrCheckoutCache($data);
        if ($checkoutCache !== false) {
            return $checkoutCache;
        }

        unset($data['quoteGiftCards']);
        unset($data['storeCreditUsed']);

        $itemChecksumData = $data['items'];

        // if there is a shipping amount, add it to the item data
        // if the shipping amount changes, we need a new checkout to ensure secondary sources are created
        // with the correct amount
        if (isset($data['shippingChoice'])) {
            $itemChecksumData['shippingAmount'] = $data['shippingChoice']['amount'];
        }

        // determine if item or shipping data has changed.  if not, a DR update is sufficient
        $itemChecksum = sha1($this->jsonSerializer->serialize($itemChecksumData));
        $existingItemChecksum = $this->session->getDrCheckoutItemChecksum();
        if (!empty($existingItemChecksum) && $itemChecksum == $existingItemChecksum) {

            // taxInclusive flag not supported with updates and item data is unchanged
            unset($data['taxInclusive']);
            unset($data['items']);
            $response['setSecondary'] = false; // do not set secondary sources on updates as they already exist

            $drCheckoutId = $this->session->getDrCheckoutId();
            $result = $this->setCheckoutUpdate($drCheckoutId, $data);
        } else {
            // if this is a new checkout, clear existing DR sourceId if present
            $this->session->unsDrSourceId();
            $this->session->setIsDrPrimarySourceAssociatedWithCheckout(false);
            $result = $this->config->doCurlPost('checkouts', $data);
        }

        $this->session->setDrCheckoutItemChecksum($itemChecksum);

        if (!$result['success']) {
            $this->session->setDrResult($this->jsonSerializer->serialize($result));
            return $result;
        }

        $productTax = 0;
        $productTotalExclTax = 0;
        $resultNew = $result['message'];

        if (isset($resultNew['taxIdentifiers']) && empty($resultNew['totalTax'])) {
            $response['taxExemption'] = "true";
        }

        if (isset($resultNew['items'])) {
            $response['items'] = $resultNew['items'];
        }
        if (isset($resultNew['items'])) {
            foreach ($resultNew['items'] as $item) {
                $productTax += $item['tax']['amount'];
                $productTotalExclTax = $productTotalExclTax
                    + $item['amount']
                    + $item['metadata']['productDiscount'];
            }
        }

        $response['productTotalExclTax'] = $this->config->round($productTotalExclTax);
        $response['productTax'] = $this->config->round($productTax);
        $response['shippingTax'] =  0;
        $response['shippingTotalExclTax'] = 0;
        $response['success'] = $result['success'];
        $result = $result['message'];
        $response['id'] = $result['id'];

        $response['subTotalDiscount'] = $result['metadata']['subTotalDiscount'] ?? 0;
        $response['shippingDiscount'] = $result['metadata']['shippingDiscount'] ?? 0;

        if (isset($result['shippingChoice'])) {
            $response['shippingTax'] = $result['shippingChoice']['taxAmount'];
            $response['shippingTotalExclTax'] = $result['shippingChoice']['amount'] +
                $response['shippingDiscount'];
        }

        $response['orderTotal'] = $this->config->round($result['totalAmount']);
        $response['orderTax'] = $this->config->round($result['totalTax']);
        $response['shippingTotalExclTax'] = $this->config->round($response['shippingTotalExclTax']);
        $response['shippingTax'] = $this->config->round($response['shippingTax']);

        if (isset($result['importerOfRecordTax']) && $result['importerOfRecordTax'] === true) {
            $response['importerOfRecordTax'] = $result['importerOfRecordTax'];
            $response['totalImporterTax'] = $result['totalImporterTax'];
            $response['totalDuty'] = $this->config->round($result['totalDuty']);
        } else {
            $result['importerOfRecordTax'] = 0;
        }
        // DR API version 2021-03-23
        if (isset($result['payment']) && isset($result['payment']['session'])) {
            $response['paymentSessionId'] = $result['payment']['session']['id'];
        } else { // DR API version 2021-02-23
            $response['paymentSessionId'] = $result['paymentSessionId'];
        }
        $response['sellingEntity'] = !empty($result['sellingEntity']) ?
            $result['sellingEntity']['id'] : $this->config->getDefaultSellingEntity();
        $this->session->setDrResult($this->jsonSerializer->serialize($response));

        return $response;
    }

    /**
     * @param string $sourceId
     * @return mixed
     */
    public function setCustomerSource(string $sourceId)
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $this->customerSession->setCurrentCustomerStoreId($currentStoreId);
        $drCustomerId = $this->customerIdManagement->getSessionDigitalRiverCustomerId();
        return $this->config->doCurlPost(
            sprintf('customers/%s/sources/%s', $drCustomerId, $sourceId),
            []
        );
    }

    /**
     * @param string $sourceId
     * @return mixed
     */
    public function deleteCustomerSource(string $sourceId)
    {
        $drCustomerId = $this->customerIdManagement->getSessionDigitalRiverCustomerId();
        return $this->config->doCurlDelete(
            sprintf('%s/customers/%s/sources/%s', $this->config->getUrl(), $drCustomerId, $sourceId),
            []
        );
    }

    /**
     * Create DR API source
     *
     * @param array $sourceInfo
     * @return array
     */
    public function setSource($sourceInfo)
    {
        $result = $this->config->doCurlPost(
            'sources/',
            $sourceInfo
        );
        return $result;
    }

    /**
     * @param $customerId
     * @return array|mixed|string
     */
    public function getCustomer($customerId)
    {
        return $this->config->doCurlGet('customers', $customerId);
    }

    /**
     * @param  mixed $data
     * @return mixed|null
     */
    public function setCheckoutUpdate($checkoutId, $data)
    {
        return $this->config->doCurlPost('checkouts/' . $checkoutId, $data);
    }

    /**
     * @param $checkoutId
     * @return mixed|null
     */
    public function getCheckout($checkoutId)
    {
        return $this->config->doCurlGet('checkouts', $checkoutId);
    }

    /**
     * @param  mixed $accessToken
     * @return mixed|null
     */
    public function setOrder($checkoutId, $upstreamId)
    {
        $data['checkoutId'] = $checkoutId;
        $data['upstreamId'] = $upstreamId;
        return $this->config->doCurlPost('orders', $data);
    }

    /**
     * @param $drOrderId
     * @return mixed|null
     */
    public function getDrOrder($drOrderId, $data)
    {
        return $this->config->doCurlGet('orders', $drOrderId, $data);
    }

    /**
     * Refresh order call only for TEST needs.
     *
     * @param $orderId
     * @param $data
     * @return array
     */
    public function refreshOrder($orderId, $data)
    {
        return $this->config->doCurlPost(sprintf('orders/%s/refresh', $orderId), $data);
    }

    /**
     * @param $order
     * @return array
     */
    public function setOrderStateComplete($order)
    {
        $request['orderId'] = $order->getDrOrderId();
        $drConnector = $this->drFactory->create();

        $drObj = $drConnector->load($order->getDrOrderId(), 'requisition_id');

        if ($drObj->getId()) {
            $lineItems = $this->jsonSerializer->unserialize($drObj->getLineItemIds());
            foreach ($lineItems as $item) {
                $dataItem = ['skuId' => $item['sku'], 'quantity' => $item['qty']];
                $request['items'][] = $dataItem;
            }
            $result = $this->config->doCurlPost('fulfillments', $request);
        }
        return ['success' => false];
    }

    /**
     * @param $data
     * @return array
     */
    public function setFileLink($data): array
    { 
        return $this->config->doCurlPost('file-links', $data);
    }

    /**
     * Creates a new Tax ID
     *
     * @param string $type
     * @param string $value
     * @return array
     */
    public function createTaxId($type, $value)
    {
        $request['type'] = $type;
        $request['value'] = $value;

        return $this->config->doCurlPost('tax-identifiers/', $request);
    }

    /**
     * Creates a new invoice attribute ID
     *
     * @param string $checkoutId
     * @param string $invoiceAttributeId
     * @return array
     */
    public function createInvoiceAttributeId($checkoutId, $invoiceAttributeId)
    {
        $request = ['invoiceAttributeId' =>  $invoiceAttributeId];
        return $this->config->doCurlPost('checkouts/' . urlencode($checkoutId), $request);
    }

    /**
     * Assign checkout to invoice attribute ID
     *
     * @param string $checkoutId
     * @param string $invoiceAttributeId
     * @return array
     */
    public function assignInvoiceAttributeToCheckout($checkoutId, $invoiceAttributeId)
    {
        $invoiceAttributeId[] = ['id' => $invoiceAttributeId];
        $request = ['invoiceAttribute' => $invoiceAttributeId];

        return $this->config->doCurlPost('checkouts/' . urlencode($checkoutId), $request);
    }

    /**
     * Assigns source to checkout
     *
     * @param string $sourceId
     * @param string $checkoutId
     * @return array
     */
    public function assignSourceToCheckout($sourceId, $checkoutId)
    {
        return $this->config->doCurlPost(
            'checkouts/' . $checkoutId . '/sources/' . $sourceId,
            []
        );
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
    public function setFulfillmentRequest($lineitems, $order)
    {
        $request['orderId'] = $order->getDrOrderId();
        $storeId = $order->getStoreId();
        $storecode = $this->storeManager->getStore($storeId)->getCode();
        foreach ($lineitems as $itemId => $item) {
            $dataItem = ['itemId' => $item['lineItemID'], 'quantity' => $item['quantity']];
            $request['items'][] = $dataItem;
            $request['metadata']['storecode'] = $storecode;
            $request['trackingCompany'] = $item['trackingCompany'] ?? '';
            $request['trackingNumber'] = $item['trackingNumber'] ?? '';
        }
        return $this->config->doCurlPost('fulfillments', $request);
    } // end: function

    public function setOrderCancellation($order)
    {
        $request['orderId'] = $order['id'];
        foreach ($order['items'] as $item) {
            $dataItem = ['itemId' => $item['id'], 'cancelQuantity' => $item['quantity']];
            $request['items'][] = $dataItem;
        }
        return $this->config->doCurlPost('fulfillments', $request);
    }

    /**
     * Function to send a fulfillment cancel request to DR when @OrderItem is cancelled from Magento Admin
     *
     * @param array  $lineItems
     * @param object $order
     *
     * @return array $result
     */
    public function setFulfillmentCancellation($lineitems, $order)
    {
        $storeId = $order->getStoreId();
        $storecode = $this->storeManager->getStore($storeId)->getCode();
        $request['orderId'] = $order->getDrOrderId();
        foreach ($lineitems as $itemId => $item) {
            $dataItem = ['itemId' => $item['lineItemID'], 'cancelQuantity' => $item['quantity']];
            $request['items'][] = $dataItem;
            $request['metadata']['storecode'] = $storecode;
        }
        return $this->config->doCurlPost('fulfillments', $request);
    }

    private function _checkRefundAmount($refundAmount, $availableToRefund, $type, $id) {
        $drAvailable = round((float)$availableToRefund, 2);
        $mageRequest = round((float)$refundAmount, 2);
        if ($drAvailable < $mageRequest) {
            $errorMessage = sprintf(
                'DR Available Refund Amount is $%f which is less than Credit Memo amount of $%f, %s id %s',
                $drAvailable,
                $mageRequest,
                $type,
                $id
            );
            $setRefundResult = array(
                'error' => true,
                'message' => $errorMessage
            );
            $this->_logger->info("DR REFUND DATA " . $this->jsonSerializer->serialize($setRefundResult));
            return $setRefundResult;
        }
        return [];
    }

    /**
     * Returns the amount of the total requested refund, or null if refund request fails
     *
     * @param CreditmemoInterface $creditmemo
     * @return array
     */
    public function setRefundRequest(CreditmemoInterface $creditmemo): ?array
    {
        $this->_logger->info("DR REFUND DRAPI");
        /** @var Order $order */
	
	// get magento credit memo id (increment id)
        if (!$creditmemo->getIncrementId()){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $entityType = $objectManager
            	->create('Magento\Eav\Model\Config')
            	->getEntityType('creditmemo');
            $creditmemo->setIncrementId($entityType->fetchNewIncrementId($creditmemo->getStoreId()));
        }
        $creditMemoId = $creditmemo->getIncrementId();

        $order = $creditmemo->getOrder();
        $orderId = $order->getDrOrderId();
        $storeId = $order->getStoreId();
        $storecode = $this->storeManager->getStore($storeId)->getCode();
        $data['metadata']['storecode']=$storecode;
        $drOrder = $this->getDrOrder($orderId, $data);
        if (!$drOrder['success']) {
            $setRefundResult = array(
                'error' => true,
                'message' => sprintf('Get DR order details failed, order id = %s', $orderId)
            );
            $this->_logger->info("DR REFUND RESULT " . $this->jsonSerializer->serialize($setRefundResult));
            return $setRefundResult;
        }
        $drAvailableToRefundAmount = isset($drOrder['message']['availableToRefundAmount']) ? $drOrder['message']['availableToRefundAmount'] : $drOrder['amount'];
        $drItemAvailableToRefundAmount;
        foreach ($drOrder['message']['items'] as $drLineItem) {
            $itemAvailableRefund = isset($drLineItem['availableToRefundAmount']) 
                ? $drLineItem['availableToRefundAmount'] 
                : $drLineItem['amount'] + $drLineItem['tax']['amount'];
            $drItemAvailableToRefundAmount[$drLineItem['id']] = $itemAvailableRefund;
        }
        
        $currencyCode = $order->getOrderCurrencyCode();
        $orderPayment = $order->getPayment();
        $storeId = $order->getStoreId();
        $storecode = $this->storeManager->getStore($storeId)->getCode();
        $creditmemoItems = $creditmemo->getAllItems();
        
        $refundData = [];
        $discount = 0.0;

        $creditMemoSubtotal = 0.0;
        $itemCreditMemoSubtotal = 0.0;
        $itemCreditMemoSubTotalDiscount = 0.0;

        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($creditmemoItems as $item) {
            if ($item->getOrderItem()->getDrOrderLineitemId() == null) {
                continue;
            }
            $refundItemId = $item->getOrderItem()->getDrOrderLineitemId();
            $data = [];
            $itemCreditMemoSubtotal = $item->getRowTotal() + $item->getTaxAmount();
            $itemCreditMemoSubTotalDiscount = $item->getDiscountAmount();
            $creditMemoItemAmount = $itemCreditMemoSubtotal - $itemCreditMemoSubTotalDiscount;
            $itemPrice = $creditMemoItemAmount / $item->getQty();
            $data['orderId'] = $orderId;
            $data['currency'] = $currencyCode;
            $data['reason'] = self::REFUND_REASON_SATISFACTION;
            $data['items'][] = [
                'itemId' => $refundItemId,
                'amount' => $itemPrice,
                'quantity' => $item->getQty()
            ];

            
            $itemIds[] = $item->getOrderItemId();
            if ($item->getWeeeTaxAppliedAmount() > 0) {
                $data['items'][] = [
                    'type' => "fees",
                    'itemId' => $item->getOrderItem()->getDrOrderLineitemId(),
                    'amount' => $item->getWeeeTaxAppliedAmount(),
                    'quantity' => $item->getQty()
                ];
                $itemCreditMemoSubtotal += $item->getWeeeTaxAppliedAmount();
            }

            $data['metadata']['magentoOrderId'] = $order->getEntityId();
            $data['metadata']['magentoOrderItemId'] = $item->getOrderItemId();
            $data['metadata']['subTotal'] = $itemCreditMemoSubtotal;
            $data['metadata']['subTotalDiscount'] = $itemCreditMemoSubTotalDiscount;
            $data['metadata']['po_number'] = $orderPayment->getPoNumber() ?? '';
            $data['metadata']['type'] = 'product';
            

            $amountCheckResult = $this->_checkRefundAmount(
                $creditMemoItemAmount, 
                $drItemAvailableToRefundAmount[$refundItemId],
                'item',
                $refundItemId
            );
            if (isset($amountCheckResult['error'])) {
                return $amountCheckResult;
            }

            $refundData[] = $data;
            $creditMemoSubtotal += $creditMemoItemAmount;
        }

        if ($creditmemo->getShippingInclTax()) {
            $discount = round(abs($creditmemo->getDiscountAmount()) - $itemCreditMemoSubTotalDiscount, 2);
            $amount = $creditmemo->getShippingAmount() + $creditmemo->getShippingTaxAmount() - $discount;
            $creditMemoSubtotal +=  $amount;

            $data = [];
            $data['orderId'] = $orderId;
            $data['currency'] = $currencyCode;
            $data['type'] = 'shipping';
            $data['amount'] = $amount;
            $data['reason'] = 'CUSTOMER_SATISFACTION_ISSUE';
            $data['metadata']['magentoOrderId'] = $order->getEntityId();
            $data['metadata']['shippingAmount'] = $creditmemo->getShippingAmount();
            $data['metadata']['shippingTaxAmount'] = $creditmemo->getShippingTaxAmount();
            $data['metadata']['shippingInclTax'] = $creditmemo->getShippingInclTax();
            $data['metadata']['shippingDiscount'] = $discount;
            $refundData[] = $data;
        }

        if ($creditmemo->getData('dr_duty_fee')) {
            $amount = $creditmemo->getData('dr_duty_fee');
            $creditMemoSubtotal +=  $amount;

            $data = [];
            $data['orderId'] = $orderId;
            $data['currency'] = $currencyCode;
            $data['type'] = 'duty';
            $data['amount'] = $amount;
            $data['reason'] = 'self::REFUND_REASON_REQUESTED';
            $refundData[] = $data;
        }

        if ($creditmemo->getData('dr_ior_tax')) {
            $amount = $creditmemo->getData('dr_ior_tax');
            $creditMemoSubtotal +=  $amount;
            $data = [];
            $data['orderId'] = $orderId;
            $data['currency'] = $currencyCode;
            $data['type'] = 'importer_tax';
            $data['percent'] = 100;
            //$data['amount'] = $amount;
            $data['reason'] = self::REFUND_REASON_REQUESTED;
            $refundData[] = $data;
        }

        // order level refund
        if ($creditmemo->getAdjustmentPositive()) {
            $amount = $creditmemo->getAdjustmentPositive();
            $creditMemoSubtotal +=  $amount;

            $data = [];
            $data['orderId'] = $orderId;
            $data['currency'] = $currencyCode;
            $data['amount'] = $amount;
            $data['reason'] = 'CUSTOMER_SATISFACTION_ISSUE';
            $data['metadata']['type'] = 'adjustment';
            $data['metadata']['magentoOrderId'] = $order->getEntityId();
            $refundData[] = $data;
        }

        $this->_logger->info("DR REFUND DATA " . $this->jsonSerializer->serialize($refundData));

        $amountCheckResult = $this->_checkRefundAmount(
            $creditMemoSubtotal, 
            $drAvailableToRefundAmount,
            'order',
            $orderId
        );
        if (isset($amountCheckResult['error'])) {
            return $amountCheckResult;
        }

        $refundedAmount = 0.0;

        $resultMessage = '';
        $hasError = false;
        foreach ($refundData as $data) {
            $data['metadata']['storecode'] = $storecode;
            $data['metadata']['magentoCreditmemoIncrementId'] = $creditMemoId;

            // send the request to DR
            $result = $this->config->doCurlPost('refunds', $data);
            $this->_logger->info("DR REFUND RESPONSE " . $this->jsonSerializer->serialize($result));
            if (!isset($result['success']) || !$result['success']) {
                $hasError = true;
                $resultMessage .= $result['message'];
                if (isset($data['type'])) {
                    $resultMessage .= '(for refund '.$data['type'].')';
                } else {
                    if (isset($data['items'])) {
                        $resultMessage .= '(for refund '.$this->jsonSerializer->serialize($data['items']).')';
                    }
                }
                $resultMessage .= "\n";
	    }

            $resultArray[] = $result;
    
            if (isset($result['message']) && isset($result['message']['id'])) {
                $this->refundRegistry->setCurrentDrRefundId($result['message']['id']);
            }
    
            if (isset($result['message']) && isset($result['message']['items'])) {
                foreach ($result['message']['items'] as $item) {
                    $refundedAmount += (float) $item['amount'];
                }
            }
    
            if (isset($result['message']) && isset($result['message']['amount'])) {
                $refundedAmount += (float) $result['message']['amount'];
            }
        }
        
        $setRefundResult;
        if (!$hasError) {
            $setRefundResult = array(
                'error' => false,
                'refundedAmount' => $refundedAmount
            );
        } else {
            $setRefundResult = array(
                'error' => true,
                'message' => $resultMessage
            );
        }
        $this->_logger->info("DR REFUND RESULT " . $this->jsonSerializer->serialize($setRefundResult));
        return $setRefundResult;
    }

    /**
     * @param string $refundId
     * @param int $creditMemoId
     *
     * @return array
     */
    public function setRefundCreditMemoId(string $refundId, int $creditMemoId, int $storeId): array
    {
        $storecode = $this->storeManager->getStore($storeId)->getCode();
        $requestData = [
            SetOfflineRefundTokenInterface::NODE_METADATA => [
                SetOfflineRefundTokenInterface::FIELD_MEMO_ID => $creditMemoId,
                "storecode" => $storecode
            ],
        ];
        return $this->config->doCurlPost('refunds/' . $refundId, $requestData);
    }

    /**
     * @param $filePath
     * @return array|string
     */
    public function uploadTaxCertificate($filePath)
    {
        return $this->fileSend->sendFile('tax_document_customer_upload', $filePath);
    }

    /**
     * @param $id
     * @param array $certificateData
     * @return array
     */
    public function addCertificate($id, array $certificateData): array
    {
        return $this->updateCustomer($id, [
            'taxCertificate' => $certificateData
        ]);
    }

    /**
     * Creating of Digital River Customer ID
     *
     * @param $customer
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function createDrId($customer): string
    {
        $drCreateId = $this->creator->getCustomerId($customer);
        $drCustomerId = $this->getOrCreateCustomerId($drCreateId, $customer->getEmail());

        if (empty($drCustomerId)) {
            throw new LocalizedException(
                __('Cannot retrieve customer information. Please contact website administrator')
            );
        }

        if ($customer instanceof CustomAttributesDataInterface) {
            //set customer id
            $customer->setCustomAttribute(AddDrCustomerId::ATTRIBUTE_CODE, $drCustomerId);
            $this->customerRepository->save($customer);
        }

        return $drCustomerId;
    }

    /**
     * @param $id
     * @param null $email
     * @return string
     * @throws LocalizedException
     */
    public function getOrCreateCustomerId($id, $email = null): string
    {
        $response = $this->getCustomer($id);
        $statusCode = $response['statusCode'];

        if (!in_array($statusCode, ['200', '404'])) {
            throw new LocalizedException(
                __('There was an error fetching the customer.')
            );
        }

        if ((int)$statusCode === 200 && is_array($response['message'])) {
            return $response['message']['id'];
        }

        $newCustomer = $this->setCustomer(['id' => $id, 'email' => $email]);
        if (!is_array($newCustomer['message'])) {
            throw new LocalizedException(
                __('Cannot create customer. Please contact website administrator')
            );
        }

        return $newCustomer['message']['id'];
    }
}
