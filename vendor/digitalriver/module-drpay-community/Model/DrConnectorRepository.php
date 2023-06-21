<?php

/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\Webhook\SetOfflineRefundTokenInterface;
use Digitalriver\DrPay\Helper\Config as DrConfig;
use Digitalriver\DrPay\Helper\Data as DrPayData;
use Digitalriver\DrPay\Logger\Logger as Logger;
use Digitalriver\DrPay\Model\DrConnectorFactory as ResourceDrConnector;
use Digitalriver\DrPay\Model\ResourceModel\InvoiceCreditMemoLinks as InvoiceCreditMemoLinksResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order as Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DrConnectorRepository extends \Magento\Framework\Model\AbstractModel
{
    public const ORDER_ACCEPTED = 'order.accepted';
    public const ORDER_COMPLETE = 'order.complete';
    public const ORDER_BLOCKED = 'order.blocked';
    public const REFUND_COMPLETE = 'refund.complete';
    public const REFUND_FAILED = 'refund.failed';
    public const REFUND_PENDING = 'refund.pending';
    public const REFUND_PENDING_INFORMATION = 'refund.pending_information';
    public const ORDER_REVIEW_OPENED = 'order.review_opened';
    public const ORDER_INVOICE_CREATED = 'order.invoice.created';
    public const ORDER_CREDIT_MEMO_CREATED = 'order.credit_memo.created';
    public const REFUND_SUCCEEDED = 'refund.complete';
    public const CHARGE_REFUNDED = 'order.charge.refund.complete';
    public const INVOICE_FILE_TYPE = 'INVOICE';
    public const CREDIT_MEMO_FILE_TYPE = 'CREDIT_MEMO';
    public const CHARGE_TYPE_CUSTOMER_CREDIT = 'customerCredit';
    public const CHARGE_TYPE_CREDIT_CARD = 'creditCard';
    public const CHARGE_TYPE_PAYPAL = 'payPal';
    public const METHOD_TYPE_CUSTOMER_CREDIT = 'Customer credit';
    public const METHOD_TYPE_CREDIT_CARD = 'Credit Card';
    public const METHOD_TYPE_PAYPAL = 'Paypal';
    public const ORDER_MESSAGE_REFUNDED = 'Refunded ';
    public const ORDER_MESSAGE_TO = ' to ';
    public const HTTP_OK = 200;
    public const HTTP_FAILED = 400;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_CONFLICT = 409;
    public const AVAILABLE_WEBHOOKS = [
        self::ORDER_ACCEPTED,
        self::ORDER_COMPLETE,
        self::ORDER_BLOCKED,
        self::REFUND_COMPLETE,
        self::REFUND_FAILED,
        self::REFUND_PENDING,
        self::REFUND_PENDING_INFORMATION,
        self::ORDER_REVIEW_OPENED,
        self::ORDER_INVOICE_CREATED,
        self::ORDER_CREDIT_MEMO_CREATED,
        self::REFUND_SUCCEEDED,
        self::CHARGE_REFUNDED,
    ];

    /**
     * @var ResourceDrConnector
     */
    protected $resource;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Digitalriver\DrPay\Model\RefundRepository
     */
    protected $refundRepository;

    /**
     * Charge repository
     *
     * @var \Digitalriver\DrPay\Model\ChargeRepository
     */
    protected $chargeRepository;

    /**
     * @var DrPayData
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DrConfig
     */
    protected $_config;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var InvoiceCreditMemoLinksResource
     */
    protected $drLinksResource;

    /**
     * @var InvoiceCreditMemoLinksFactory
     */
    protected $drLinksFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * DrConnectorRepository constructor.
     *
     * @param DrConnectorFactory $resource
     * @param OrderFactory $orderFactory
     * @param OrderRepository $orderRepository
     * @param ChargeRepository $chargeRepository
     * @param RefundRepository $refundRepository
     * @param OrderResource $orderResource
     * @param Invoice $invoice
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param InvoiceService $invoiceService
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoService $creditmemoService
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param InvoiceCreditMemoLinksResource $drLinksResource
     * @param InvoiceCreditMemoLinksFactory $drLinksFactory
     * @param StoreManagerInterface $storeManager
     * @param Transaction $transaction
     * @param DrPayData $helper
     * @param Logger $logger
     * @param DrConfig $config
     * @param Json $jsonSerializer
     * @param EventManager $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ResourceDrConnector $resource,
        OrderFactory $orderFactory,
        OrderRepository $orderRepository,
        ChargeRepository $chargeRepository,
        RefundRepository $refundRepository,
        OrderResource $orderResource,
        Invoice $invoice,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceService $invoiceService,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService,
        CreditmemoRepositoryInterface $creditmemoRepository,
        InvoiceCreditMemoLinksResource $drLinksResource,
        InvoiceCreditMemoLinksFactory $drLinksFactory,
        StoreManagerInterface $storeManager,
        Transaction $transaction,
        DrPayData $helper,
        Logger $logger,
        DrConfig $config,
        Json $jsonSerializer,
        EventManager $eventManager,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->orderFactory = $orderFactory;
        $this->chargeRepository = $chargeRepository;
        $this->refundRepository = $refundRepository;
        $this->orderRepository = $orderRepository;
        $this->orderResource = $orderResource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceService = $invoiceService;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->drLinksResource = $drLinksResource;
        $this->drLinksFactory = $drLinksFactory;
        $this->storeManager = $storeManager;
        $this->invoice = $invoice;
        $this->transaction = $transaction;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->_config = $config;
        $this->jsonSerializer = $jsonSerializer;
        $this->eventManager = $eventManager;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * DRAPI Event handler
     */
    public function saveEventRequest($payload)
    {
        try {
            $data = (is_array($payload)) ? $payload : $this->jsonSerializer->unserialize($payload);
            if (isset($data['type'])) {
                switch ($data['type']) {
                    case self::ORDER_ACCEPTED:
                        $response = $this->setOrderStatus($data, self::ORDER_ACCEPTED);
                        $this->logger->info($this->jsonSerializer->serialize($response));
                        $this->dispatchOrderAcceptedEvent($data);
                        break;
                    case self::ORDER_COMPLETE:
                        $response = $this->saveOrderCompleteRequest($data);
                        $this->logger->info($this->jsonSerializer->serialize($response));
                        break;
                    case self::ORDER_BLOCKED:
                        $response = $this->setOrderStatus($data, self::ORDER_BLOCKED);
                        $this->logger->info($this->jsonSerializer->serialize($response));
                        break;
                    case self::REFUND_COMPLETE:
                        $response = $this->saveRefundCompleteRequest($data['data']['object']);
                        break;
                    case self::REFUND_FAILED:
                        $response = $this->saveRefundFailedRequest($data);
                        $this->logger->info($this->jsonSerializer->serialize($response));
                        break;
                    case self::REFUND_PENDING:
                        $response = $this->saveRefundRequest($data);
                        break;
                    case self::REFUND_PENDING_INFORMATION:
                        $response = $this->persistOfflineRefundToken($data['data']['object']);
                        break;
                    case self::ORDER_REVIEW_OPENED:
                        $response = $this->saveOrderReviewRequest($data);
                        $this->logger->info($this->jsonSerializer->serialize($response));
                        break;
                    case self::ORDER_INVOICE_CREATED:
                    case self::ORDER_CREDIT_MEMO_CREATED:
                        $response = $this->saveInvoiceOrCreditMemoCreatedRequest($data);
                        $this->logger->info($this->jsonSerializer->serialize($response));
                        break;
                    case self::CHARGE_REFUNDED:
                        $response = $this->saveRefundSucceededRequest($data);
                        $this->logger->info($this->jsonSerializer->serialize($response));

                        if ($response['success']) {
                            $response = $this->saveChargeRefundSucceededRequest($data);
                            $this->logger->info($this->jsonSerializer->serialize($response));
                        }
                        break;
                    default:
                        $response = $this->getDefaultResponse();
                }
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    private function dispatchOrderAcceptedEvent(array $data): void
    {
        $eventData = ['webhook_object' => $data['data']['object']];
        $this->eventManager->dispatch(str_replace('.', '_', self::ORDER_ACCEPTED) . '_webhook', $eventData);
    }

    /**
     * @param array $data
     * @return array => [
     *  'success' => (bool),
     *  'message' => (string),
     *  'statusCode' => (int),
     * ]
     */
    private function persistOfflineRefundToken(array $data): array
    {
        $response = [
            'success' => true,
            'message' =>'Request successfully processed',
            'statusCode' => self::HTTP_OK
        ];
        try {
            if (!isset($data['metadata']['magentoCreditmemoId'])) {
                $orders = $this->getOrdersList($data);
                if (count($orders) > 0) {
                    $orderId = $orders[0]->getEntityId();
                    $magentoCreditmemoIncrementId = $data['metadata']['magentoCreditmemoIncrementId'];
                    $magentoCreditMemoId = $this->_getCreditMemoId($orderId, $magentoCreditmemoIncrementId);
                    $data['metadata']['magentoCreditmemoId'] = $magentoCreditMemoId;
                }
            }
            $eventData = [
                'refund_data' => $data,
            ];
            $this->logger->info("\nWEBHOOK ".__FUNCTION__." EVENT: ".$this->jsonSerializer->serialize($eventData)."\n");
            $this->eventManager->dispatch(str_replace('.', '_', self::REFUND_PENDING_INFORMATION), $eventData);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Request failed: '.$e->getMessage(),
                'statusCode' => self::HTTP_NOT_FOUND,
            ];
        }
        return $response;
    }

    private function _getCreditMemoId($drOrderId, $mageIncrementId) {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $drOrderId, 'entity_id');
        $creditMemos = $this->getCreditMemoList($order->getEntityId());
        $creditMemoId;
        if (count($creditMemos) > 0) {
            foreach ($creditMemos as $memo){
                $memoData = $memo->getData();
                if ($memoData['increment_id'] == $mageIncrementId) {
                    $creditMemoId = $memo->getEntityId();
                }
            }
        }
        return $creditMemoId;
    }

    private function getDefaultResponse()
    {
        return ['success' => true,
        'message' => 'No action taken',
        'statusCode' => self::HTTP_OK];
    }

    /**
     * Function to save failed refund request
     *
     * @param array $data
     * @return array
     * @throws CouldNotSaveException
     */
    private function saveRefundFailedRequest(array $data): array
    {
        $response = ['success' => false,
        'message' => 'Request failed',
        'statusCode' => self::HTTP_FAILED];
        try {
            $orders = $this->getOrdersList($data);
            if (count($orders) > 0) {
                $order = $this->orderFactory->create();
                $this->orderResource->load($order, $orders[0]->getEntityId(), 'entity_id');
                $creditMemos = $this->getCreditMemoList($order->getEntityId());
                if (count($creditMemos) > 0) {
                    $creditMemo = $this->creditmemoRepository->get($creditMemos[0]->getEntityId());
                    $creditMemo->setState(Creditmemo::STATE_CANCELED);
                    $this->creditmemoRepository->save($creditMemo);
                    $failureReason = $data['data']['object']['failureReason'];
                    $order->addCommentToStatusHistory('Refund failed: ' . $failureReason);
                    $this->orderRepository->save($order);
                }
                $response = ['success' => true,
                    'message' => 'Request successfully processed',
                    'statusCode' => self::HTTP_OK
                ];
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    /**
     * Function to save refund request
     *
     * @param array $data
     * @return array
     * @throws CouldNotSaveException
     */
    private function saveRefundRequest(array $data): array
    {
        try {
            $eventData = [
                'refund_data' => $data['data']['object'],
            ];

            $orders = $this->getOrdersList($data);
            $creditmemoData = [];
            if (count($orders) > 0) {
                $order = $this->orderFactory->create();
                $this->orderResource->load($order, $orders[0]->getEntityId(), 'entity_id');
                $creditMemos = $this->getCreditMemoList($order->getEntityId());
                $creditMemoId;

                $orderId = $orders[0]->getEntityId();
                $magentoCreditmemoIncrementId = $data['data']['object']['metadata']['magentoCreditmemoIncrementId'];
                $magentoCreditMemoId = $this->_getCreditMemoId($orderId, $magentoCreditmemoIncrementId);
                $eventData['refund_data']['metadata']['magentoCreditmemoId'] = $magentoCreditMemoId;
  
                $response = [
                    'success' => true,
                    'message' =>'Request successfully processed',
                    'statusCode' => self::HTTP_OK
                ];
            }

            $this->logger->info("\nWEBHOOK ".__FUNCTION__." EVENT: ".$this->jsonSerializer->serialize($eventData)."\n");
            $this->eventManager->dispatch(str_replace('.', '_', self::REFUND_PENDING), $eventData);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Request failed: '.$e->getMessage(),
                'statusCode' => self::HTTP_NOT_FOUND,
            ];
        }
        return $response;
    }

    /**
     * Function to save refund request
     *
     * @param array $data
     * @return array
     * @throws CouldNotSaveException
     */
    private function saveRefundCompleteRequest(array $data): array
    {
        $response = [
            'success' => true,
            'message' =>'Request successfully processed',
            'statusCode' => self::HTTP_OK
        ];
        try {
            if (!isset($data['metadata']['magentoCreditmemoId'])) {
                $orders = $this->getOrdersList($data);
                if (count($orders) > 0) {
                    $orderId = $orders[0]->getEntityId();
                    $magentoCreditmemoIncrementId = $data['metadata']['magentoCreditmemoIncrementId'];
                    $magentoCreditMemoId = $this->_getCreditMemoId($orderId, $magentoCreditmemoIncrementId);
                    $data['metadata']['magentoCreditmemoId'] = $magentoCreditMemoId;
                }
            }
            $eventData = [
                'refund_data' => $data,
            ];
            $this->logger->info("\nWEBHOOK ".__FUNCTION__." EVENT: ".$this->jsonSerializer->serialize($eventData)."\n");
            $this->eventManager->dispatch(str_replace('.', '_', self::REFUND_COMPLETE), $eventData);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Request failed: '.$e->getMessage(),
                'statusCode' => self::HTTP_NOT_FOUND,
            ];
        }
        return $response;
    }

    private function setOrderStatus($data, $webhook)
    {
        $comment = '';
        $state = '';
        switch ($webhook) {
            case 'order.accepted':
                $comment = 'Order accepted';
                $state = Order::STATE_PROCESSING;
                break;
            case 'order.blocked':
                $comment = 'Suspected fraud';
                $state = Order::STATUS_FRAUD;
                break;
        }
        $response = ['success' => false,
        'message' => 'Request failed',
        'statusCode' => self::HTTP_FAILED];
        try {
            $requestObj = $data['data']['object'];
            $requisition_id = $requestObj['id'];
            if ($requisition_id) {
                $order = $this->orderFactory->create()->load($requisition_id, 'dr_order_id');
                if ($order->getId() && in_array(
                    $order->getState(),
                    [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW]
                )
                ) {
                    $this->logger->info("ORDER IN " . $order->getState() . " STATE FOR " . $requisition_id);
                    //update order status to processing as OFI means payment received
                    $order->setDrOrderState($requestObj['state']);
                    $order->setState($state);
                    $order->setStatus($state);
                    $order->addStatusHistoryComment(__($comment));
                    $order->save();
                    if ($state == Order::STATUS_FRAUD && $order->canCancel()) {
                        $order->cancel();
                        $order->addStatusHistoryComment(__('Canceled'));
                        $order->save();
                    }
                    $this->logger->info("ORDER UPDATED TO " . $order->getState() . " STATE FOR " . $requisition_id);
                    $response = ['success' => true,
                    'message' => 'Request successfully processed',
                    'statusCode' => self::HTTP_OK];
                } elseif ($order->getId() && in_array(
                    $order->getState(),
                    [\Magento\Sales\Model\Order::STATE_PROCESSING,
                    \Magento\Sales\Model\Order::STATUS_FRAUD]
                )
                    ) {
                    $response = ['success' => true,
                        'message' => 'The request has been successfully processed by Magento',
                        'statusCode' => self::HTTP_OK];
                }
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    private function saveOrderCompleteRequest($data)
    {
        try {
            $requestObj = $data['data']['object'];
            $requisition_id = $requestObj['id'];
            $response = ['success' => false,
                'message' => 'Request failed',
                'statusCode' => self::HTTP_FAILED];

            if ($requisition_id) {
                $order = $this->orderFactory->create()->load($requisition_id, 'dr_order_id');

                if (!$order->canInvoice() && $order->getId() && in_array($order->getState(), [
                            \Magento\Sales\Model\Order::STATE_PROCESSING,
                            \Magento\Sales\Model\Order::STATE_COMPLETE
                        ])
                ) {
                    $this->logger->info("ORDER IN " . $order->getState() . " STATE FOR " . $requisition_id);
                    $order->setDrOrderState($requestObj['state']);

                    $order->setState(Order::STATE_COMPLETE);
                    $order->setStatus(Order::STATE_COMPLETE);
                    $order->addStatusHistoryComment(__('Order complete'));
                    $order->save();

                    $model = $this->resource->create();
                    $model->load($order->getDrOrderId(), 'requisition_id');
                    if ($model->getId()) {
                        $model->setPostStatus(1);
                        $model->save();
                    }
                    $response = ['success' => true,
                        'message' => 'Request successfully processed',
                        'statusCode' => self::HTTP_OK];
                }
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    private function saveOrderReviewRequest($data)
    {
        $response = ['success' => false,'message' => 'Request failed', 'statusCode' => self::HTTP_FAILED];
        try {
            $requestObj = $data['data']['object'];
            $requisition_id = $requestObj['id'];
            $line_item_ids = $requestObj['items'];

            if ($requisition_id) {
                $order = $this->orderFactory->create()->load($requisition_id, 'dr_order_id');
                if ($order->getId() && in_array(
                    $order->getState(),
                    [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]
                )
                ) {
                    $this->logger->info("ORDER IN " . $order->getState() . " STATE FOR " . $requisition_id);
                    //update order status to processing as OFI means payment received
                    $order->setDrOrderState($requestObj['state']);
                    $order->setState(Order::STATE_PAYMENT_REVIEW);
                    $order->setStatus(Order::STATE_PAYMENT_REVIEW);
                    $order->addStatusHistoryComment(__('Payment review'));
                    $order->save();

                    $response = ['success' => true,
                        'message' => 'The request has been successfully processed by Magento',
                        'statusCode' => self::HTTP_OK];
                } elseif ($order->getId() && in_array(
                    $order->getState(),
                    [\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW]
                )
                    ) {
                    $response = ['success' => true,
                        'message' => 'The request has been successfully processed by Magento',
                        'statusCode' => self::HTTP_OK];
                }
            } else {
                $response = ['success' => false,
                'message' => 'Failed to updated in Magento',
                'statusCode' => self::HTTP_FAILED];
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    /**
     * Creates invoice or CM in Magento and saves details from DR
     *
     * @param array $data
     * @return array
     * @throws CouldNotSaveException
     */
    private function saveInvoiceOrCreditMemoCreatedRequest($data): array
    {
        $response = ['success' => false,'message' => 'Request failed', 'statusCode' => self::HTTP_FAILED];
        try {
            $this->logger->info("Request body:" . $this->jsonSerializer->serialize($data));

            $requestObject = $data['data']['object'];
            $orders = $this->getOrdersList($data);

            if (count($orders) > 0) {
                $order = $this->orderFactory->create();
                $this->orderResource->load($order, $orders[0]->getEntityId(), 'entity_id');
                if ($data['type'] === self::ORDER_INVOICE_CREATED) {
                    $isSaved = $this->createInvoice($order, $requestObject);
                } else {
                    $isSaved = $this->createCreditMemo($order, $requestObject);
                }

                if ($isSaved === true) {
                    $response = ["success" => true, "message" => "Process Successful", "statusCode" => self::HTTP_OK];
                }
            }
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    /**
     * Creates an invoice and stores invoice URL from DR
     *
     * @param Order $order
     * @param array $data
     * @return bool
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    private function createInvoice(Order $order, array $data): bool
    {
        $isSaved = false;

        //Checks for fileId field in the request before creating a file-link
        if (isset($data['fileId']) && trim($data['fileId']) !== "") {

            // if the file_id already exists, we do not need to process
            $drFile =  $this->helper->getDrFile($data['fileId']);
            if (!$drFile->isEmpty()) {
                return true;
            }

            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $this->invoice->save($invoice);
            }

            $storeId=$order->getStoreId();
            $storecode = $this->storeManager->getStore($storeId)->getCode();
            //Makes the request to create a file-link and gets back the URL
            $response = $this->helper->setFileLink($data['fileId'],$storecode);

            //Checks if the file-link URL is provided in the response
            if ($this->validateFileLink($response)) {
                $this->saveDrFileLink($order, self::INVOICE_FILE_TYPE, $data, $response);
                $isSaved = true;
            }
        }
        return $isSaved;
    }

    /**
     * Creates Credit memo and saves file-link from DR
     *
     * @param Order $order
     * @param array $data
     * @return bool
     * @throws LocalizedException
     */
    private function createCreditMemo(Order $order, array $data): bool
    {
        $isSaved = false;

        //Checks for fileId field in the request before creating a file-link
        if (isset($data['fileId']) && trim($data['fileId']) !== "") {

            // if the file_id already exists, we do not need to process
            $drFile =  $this->helper->getDrFile($data['fileId']);
            if (!$drFile->isEmpty()) {
                return true;
            }

            $searchCriteria = $this->searchCriteriaBuilder->addFilter('order_id', $order->getEntityId())->create();
            $creditMemoList = $this->creditmemoRepository->getList($searchCriteria);
            $creditMemos = $creditMemoList->getItems();

            //Creates a credit memo for the order if it is not present in Magento
            if (count($creditMemos) === 0) {
                $creditMemo = $this->creditmemoFactory->createByOrder($order);
                $this->creditmemoRepository->save($creditMemo);
            }

            $storeId=$order->getStoreId();
            $storecode = $this->storeManager->getStore($storeId)->getCode();
            //Makes the request to create a file-link and gets back the URL
            $response = $this->helper->setFileLink($data['fileId'],$storecode);

            //Checks if the file-link URL is provided in the response
            if ($this->validateFileLink($response)) {
                $this->saveDrFileLink($order, self::CREDIT_MEMO_FILE_TYPE, $data, $response);
                $isSaved = true;
            }
        }
        return $isSaved;
    }

    /**
     * Validates whether the URL is active and accessible
     *
     * @param array $response
     * @return bool
     */
    private function validateFileLink(array $response): bool
    {
        if (isset($response['message']) &&
            isset($response['message']['expired']) &&
            !$response['message']['expired'] &&
            isset($response['message']['url']) &&
            trim($response['message']['url']) !== "" &&
            isset($response['createdAt']) &&
            trim($response['createdAt']) !== ""
        ) {
            return true;
        }
        return false;
    }

    /**
     * Creates an entry in DR File Links table
     *
     * @param Order $order
     * @param string $type
     * @param array $data
     * @param array $response
     * @throws AlreadyExistsException
     */
    private function saveDrFileLink(Order $order, string $type, array $data, array $response): void
    {
        $drLinks = $this->drLinksFactory->create();
        $drLinks->setSalesOrderId($order->getId());
        $drLinks->setDrFileType($type);
        $drLinks->setDrFileId($data['fileId']);
        $drLinks->setDrFileLinkUrl($response['message']['url']);
        $drLinks->setDrFileLinkCreatedAt($response['createdAt']);
        $this->drLinksResource->save($drLinks);
    }

    /**
     * Updates charge refund
     *
     * @param array $data
     * @return array
     * @throws CouldNotSaveException
     */
    public function saveChargeRefundSucceededRequest(array $data): array
    {
        $response = ['success' => false,
            'message' => 'Request failed',
            'statusCode' => self::HTTP_FAILED
        ];
        try {
            $orders = $this->getOrdersList($data);
            if (count($orders) > 0) {
                $order = $this->orderFactory->create();
                $this->orderResource->load($order, $orders[0]->getEntityId(), 'entity_id');

                $charges = $this->getChargesList($data);
                $this->processCharge($charges, $data, $order);
            }
            $response = ['success' => true,
                                    'message' => 'Request successfully processed',
                                    'statusCode' => self::HTTP_OK
                                    ];
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    /**
     * @param $refund
     * @param $charge
     * @param Order $order
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function processRefund($refund, $charge, Order $order): void
    {
        // see if this refund was already processed
        $previousRefund = $this->refundRepository->getById($refund['id']);

        // if this refund was NOT processed yet
        if ($previousRefund->getId() === null) {
            $refundedAmount = $refund['amount'];
            $this->updateOrder($order, $charge, $refundedAmount);
            $this->logger->info("WRITING REFUND ID TO DB: " . $refund['id']);

            // save the refund so it isn't processed again
            $this->refundRepository->saveRefund($refund['id'], $refundedAmount);
        } else {
            $this->logger->info("FOUND REFUND " . $previousRefund->getId() . ". Skipping ");
        }
    }

    /**
     * @param array $charges
     * @param array $data
     * @param Order $order
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function processCharge(array $charges, array $data, Order $order): void
    {
        foreach ($charges as $charge) {
            $this->logger->info("CHARGES " . $this->jsonSerializer->serialize($charge->toArray()));
            $refunds = $this->getRefundsFromWebhook($data, $charge->getDrSourceId());
            $this->logger->info("REFUNDS " . $this->jsonSerializer->serialize($refunds));
            if (is_array($refunds) && !empty($refunds)) {
                foreach ($refunds as $refund) {
                    $this->processRefund($refund, $charge, $order);
                }
            }
        }
    }

    /**
     * Calculates from the list of DR charge refunds, the total amount to refund
     *
     * @param array $drCharge
     * @return float
     */
    private function calculateAmountToRefund(array $drCharge): float
    {
        $amount = 0.0;
        if (isset($drCharge['refunds'])) {
            $refunds = $drCharge['refunds'];
            foreach ($refunds as $refund) {
                if (isset($refund['amount'])) {
                    $amount = floatval($refund['amount']);
                }
            }
        }
        return $amount;
    }

    /**
     * Updates sales order flat table
     *
     * @param Order $order
     * @param Charge $charge
     * @param float $refundedAmount
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    private function updateOrder(Order $order, Charge $charge, float $refundedAmount)
    {
        $currency = $order->getOrderCurrencyCode();
        $storeId = $order->getStoreId();
        $currencySymbol = $this->priceCurrency->getCurrencySymbol($storeId, $currency);
        $comment = $this->generateOrderComment($charge, $refundedAmount, $currencySymbol);
        if ($comment) {
            $order->addCommentToStatusHistory($comment, false, true);
            $this->orderRepository->save($order);
        }
    }

    /**
     * Generates order comment based on payment method used
     *
     * @param Charge $charge
     * @param float $refundedAmount
     * @param string $currency
     * @return string
     */
    private function generateOrderComment(Charge $charge, float $refundedAmount, string $currency): string
    {
        $comment = self::ORDER_MESSAGE_REFUNDED . $currency . $refundedAmount . self::ORDER_MESSAGE_TO;
        switch ($charge->getDrSourceType()) {
            case self::CHARGE_TYPE_CUSTOMER_CREDIT:
                $comment .= 'Store Credit';
                break;
            case self::CHARGE_TYPE_CREDIT_CARD:
                $comment .= self::METHOD_TYPE_CREDIT_CARD;
                break;
            case self::CHARGE_TYPE_PAYPAL:
                $comment .= self::METHOD_TYPE_PAYPAL;
                break;
            default:
                $comment .= ucwords($charge->getDrSourceType());
                break;
        }
        return $comment;
    }

    /**
     * Updates refund as successful and credits the amount
     *
     * @param array $data
     * @return array
     * @throws CouldNotSaveException
     */
    public function saveRefundSucceededRequest(array $data): array
    {
        $response = ['success' => false,
            'message' => 'Request failed',
            'statusCode' => self::HTTP_FAILED
        ];
        try {
            $orders = $this->getOrdersList($data);
            if (count($orders) > 0) {
                $order = $this->orderFactory->create();
                $this->orderResource->load($order, $orders[0]->getEntityId(), 'entity_id');
                $creditMemos = $this->getCreditMemoList($order->getEntityId());
                if (count($creditMemos) > 0) {
                    //Update refund status
                    $creditMemo = $this->creditmemoRepository->get($creditMemos[0]->getEntityId());
                    $creditMemo->setState(Creditmemo::STATE_REFUNDED);
                    $this->creditmemoRepository->save($creditMemo);

                    $response = ['success' => true,
                        'message' => 'Request successfully processed',
                        'statusCode' => self::HTTP_OK
                    ];
                }
            }
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $response;
    }

    /**
     * Returns the list of orders with the DR Order ID
     *
     * @param array $data
     * @return array
     */
    private function getOrdersList(array $data): array
    {
        //Gets the respective order using DR order ID provided in the request
        $requisitionId = isset($data['data']['object']['orderId']) ? $data['data']['object']['orderId'] : $data['orderId'];
        $filter = $this->searchCriteriaBuilder->addFilter('dr_order_id', $requisitionId, 'eq')->create();
        $orderList = $this->orderRepository->getList($filter);
        return array_values($orderList->getItems());
    }

    /**
     * Returns the list of charges with the DR Charge ID
     *
     * @param array $data
     * @return array
     */
    private function getChargesList(array $data): array
    {
        //Gets the respective order using DR order ID provided in the request

        $drOrderId = $data['data']['object']['orderId'];
        $drSourceId = $data['data']['object']['sourceId'];

        $filter = $this->searchCriteriaBuilder->addFilter(
            'dr_order_id',
            $drOrderId,
            'eq'
        )->create();

        $chargeList = $this->chargeRepository->getList($filter);
        return array_values($chargeList->getItems());
    }

    /**
     * Returns the list of credit memos for the order
     *
     * @param string $orderId
     * @return array
     */
    private function getCreditMemoList(string $orderId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('order_id', $orderId)->create();
        $creditMemoList = $this->creditmemoRepository->getList($searchCriteria);
        return array_values($creditMemoList->getItems());
    }

    /**
     * COMAPI Save fulfillment
     */
    public function saveFulFillment($OrderLevelElectronicFulfillmentRequest)
    {
        $response = [];
        $lineItemIds = [];
        $electronicFulfillmentNotices = [(object) []];
        $requisitionId = $OrderLevelElectronicFulfillmentRequest['requisitionID'];
        $lineItemsIds = $OrderLevelElectronicFulfillmentRequest['lineItemLevelRequest'];
        $requestObj = $this->jsonSerializer->serialize($OrderLevelElectronicFulfillmentRequest);
        // Getting lineItemids
        if (is_array($lineItemsIds) && isset($lineItemsIds['quantity'])) {
            $lineItemIds[] = ['qty' => $lineItemsIds['quantity'],'lineitemid'=>$lineItemsIds['lineItemID']];
        } else {
            foreach ($lineItemsIds as $lineItemid) {
                if (is_array($lineItemid)) {
                    $lineItemIds[] = ['qty' => $lineItemid['quantity'],'lineitemid'=>$lineItemid['lineItemID']];
                }
            }
        }
        $data = [ 'requisition_id' => $requisitionId,
        'request_obj' => $requestObj,
        'line_item_ids'=> $this->jsonSerializer->serialize($lineItemIds)];
        try {
            if ($requisitionId) {
                $order = $this->orderFactory->create()->load($requisitionId, 'dr_order_id');
                if ($order->getId() && $order->getStatus() != \Magento\Sales\Model\Order::STATE_CANCELED) {
                    $model = $this->resource->create();
                    $model->load($order->getDrOrderId(), 'requisition_id');
                    if (!$model->getId() || $order->getDrOrderState() != "Submitted") {
                        if ($order->getDrOrderState() != "Submitted") {
                            //update order status to processing as OFI means payment received
                            $order->setDrOrderState("Submitted");
                            $order->setState(Order::STATE_PROCESSING);
                            $order->setStatus(Order::STATE_PROCESSING);
                            $order->save();
                        }
                        $model->setData($data);
                        $model->save();
                        $response = ['ElectronicFulfillmentResponse' => [
                                "responseMessage" => "The request has been successfully processed by Magento",
                                "successful" => "true",
                                "isAutoRetriable" => "false",
                                "electronicFulfillmentNotices" => $electronicFulfillmentNotices
                            ]
                        ];
                    } else {
                        $response = ['ElectronicFulfillmentResponse' => [
                                "responseMessage" => "The request has already saved in Magento",
                                "successful" => "false",
                                "isAutoRetriable" => "false",
                                "electronicFulfillmentNotices" => $electronicFulfillmentNotices
                            ]
                        ];
                    }
                }
            } else {
                $response = ['ElectronicFulfillmentResponse' => [
                        "responseMessage" => "Please Provide the requisitionID.",
                        "successful" => "false",
                        "isAutoRetriable" => "false",
                        "electronicFulfillmentNotices" => $electronicFulfillmentNotices
                    ]
                ];
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $response;
    }

    /**
     * Gets Charge object from webhook based on $drChargeId
     *
     * @param array $data
     * @param string $drChargeId
     * @return array | null
     */
    protected function getRefundsFromWebhook(array $data, $drSourceId): ?array
    {
        if (isset($data['data']['object']['refunds']) &&
            is_array($data['data']['object']['refunds']) &&
            $data['data']['object']['sourceId'] == $drSourceId) {
            return $data['data']['object']['refunds'];
        }
        return null;
    }
}
