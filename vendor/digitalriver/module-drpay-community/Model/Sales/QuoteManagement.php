<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Sales;

use Digitalriver\DrPay\Api\ChargeRepositoryInterface;
use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;
use Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface;
use Digitalriver\DrPay\Api\PlaceOrderResultBuilderInterface;
use Digitalriver\DrPay\Api\QuoteManagementInterface;
use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Helper\Data;
use Digitalriver\DrPay\Model\DrConnectorRepository;
use Digitalriver\DrPay\Model\PrimarySourceValidatorPool;
use Digitalriver\DrPay\Model\SourceNameProviderPool;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class QuoteManagement
 *
 * Handles Digital River order placement process
 */
class QuoteManagement implements QuoteManagementInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var ChargeRepositoryInterface
     */
    private $chargeRepository;
    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;
    /**
     * @var DigitalRiverCustomerIdManagementInterface
     */
    private $digitalRiverCustomerIdManagement;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var PrimarySourceValidatorPool
     */
    private $primarySourceValidatorPool;
    /**
     * @var SourceNameProviderPool
     */
    private $sourceNameProviderPool;
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var array
     */
    private $apiResult = [];
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var PlaceOrderResultBuilderInterface
     */
    private $resultBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * QuoteManagement constructor.
     * @param Data $helper
     * @param ChargeRepositoryInterface $chargeRepository
     * @param CartManagementInterface $quoteManagement
     * @param DigitalRiverCustomerIdManagementInterface $digitalRiverCustomerIdManagement
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param EventManager $eventManager
     * @param PrimarySourceValidatorPool $primarySourceValidatorPool
     * @param SourceNameProviderPool $sourceNameProviderPool
     * @param CartRepositoryInterface $quoteRepository
     * @param PlaceOrderResultBuilderInterface $resultBuilder
     * @param SerializerInterface $jsonSerializer
     * @param Config $config
     */
    public function __construct(
        Data $helper,
        ChargeRepositoryInterface $chargeRepository,
        CartManagementInterface $quoteManagement,
        DigitalRiverCustomerIdManagementInterface $digitalRiverCustomerIdManagement,
        Session $checkoutSession,
        LoggerInterface $logger,
        EventManager $eventManager,
        PrimarySourceValidatorPool $primarySourceValidatorPool,
        SourceNameProviderPool $sourceNameProviderPool,
        CartRepositoryInterface $quoteRepository,
        PlaceOrderResultBuilderInterface $resultBuilder,
        SerializerInterface $jsonSerializer,
        Config $config
    ) {
        $this->helper = $helper;
        $this->chargeRepository = $chargeRepository;
        $this->quoteManagement = $quoteManagement;
        $this->digitalRiverCustomerIdManagement = $digitalRiverCustomerIdManagement;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->primarySourceValidatorPool = $primarySourceValidatorPool;
        $this->sourceNameProviderPool = $sourceNameProviderPool;
        $this->jsonSerializer = $jsonSerializer;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->resultBuilder = $resultBuilder;
        $this->config = $config;
    }

    /**
     * @param int $cartId
     * @param string $checkoutId
     * @param string $paymentSessionId
     * @param string|null $primarySourceId
     * @param bool $updateCheckout
     * @param string|null $savedSourceId
     *
     * @return PlaceOrderResultInterface
     * @throws LocalizedException
     */
    public function placeOrder(
        int $cartId,
        string $checkoutId,
        string $paymentSessionId,
        ?string $primarySourceId = null,
        bool $updateCheckout = true,
        ?string $savedSourceId = null
    ): PlaceOrderResultInterface {
        $quote = $this->quoteRepository->get($cartId);
        if ($quote->getReservedOrderId() === null) {
            // Reserving and saving the order increment ID on the quote to avoid multiple generations for the same quote
            $quote->reserveOrderId();
            $quote->save();
        }
        if (!$this->isQuoteActive($quote)) {
            throw new LocalizedException(__('Unable to place order.'));
        }

        if ($primarySourceId !== null) {
            $primarySourceErrors = $this->primarySourceValidatorPool->execute($primarySourceId, $quote);
            if (!empty($primarySourceErrors)) {
                foreach ($primarySourceErrors as $error) {
                    $this->logger->error($error);
                }
                throw new LocalizedException(__('Unable to place order.'));
            }
        }

        // get items in the DR checkout
        $checkoutItemQuantitiesShippingAmount = $this->helper->getCheckoutItemQuantitiesShippingAmount($checkoutId);

        $data = [];
        if (!$quote->getIsVirtual() && $quote->getShippingAddress()) {
            $shippingAddress = $quote->getShippingAddress();
            if (empty($shippingAddress->getCity()) && $shippingAddress->getSameAsBilling()) {
                $shippingAddress = $quote->getBillingAddress();
            }

            $quoteShippingAmount = $this->config->round($shippingAddress->getShippingInclTax() -
            $shippingAddress->getShippingTaxAmount() - $shippingAddress->getShippingDiscountAmount());

            // validate that Magento shipping amount is what the DR checkout has as well
            if ($quoteShippingAmount != $checkoutItemQuantitiesShippingAmount['shipping']['amount']) {
                $this->logger->error('DR checkout not synced - ' . $quoteShippingAmount . ' - ' .
                    $checkoutItemQuantitiesShippingAmount['shipping']['amount']);
                throw new LocalizedException(__('Unable to place order'));
            }

            $data['shipTo'] = $this->helper->getDrAddress($shippingAddress);
        }

        $upstreamId = $quote->getReservedOrderId();
        if ($upstreamId) {
            $data['upstreamId'] = $upstreamId;
        }

        $drCheckoutItems = $checkoutItemQuantitiesShippingAmount['items'];
        // get checkout data based on the current Magento quote
        $quoteItems = $this->helper->getSetCheckoutItemData($quote);

        $quoteItemNotFound = false;
        // use elimination to determine if the quote and checkout are synced
        foreach ($quoteItems as $quoteItem) {
            $itemId = $quoteItem['metadata']['magento_quote_item_id'];
            $itemQty = $quoteItem['quantity'];
            if (isset($drCheckoutItems[$itemId]) && $drCheckoutItems[$itemId] == $itemQty) {
                unset($drCheckoutItems[$itemId]);
            } else {
                $this->logger->error('Quote item not found - ' . $this->jsonSerializer->serialize($quoteItem));
                $quoteItemNotFound = true;
            }
        }

        // if the dr checkout array still has items, they are not synced
        if (!empty($drCheckoutItems) || $quoteItemNotFound) {
            $this->logger->error('DR checkout not synced - ' .
                $this->jsonSerializer->serialize($drCheckoutItems));
            throw new LocalizedException(__('Unable to place order: checkout not synced'));
        }

        // validate billing address against magento settings as it may change via payment provider
        $billingAddress = $quote->getBillingAddress();
        $billingAddressValidation = $billingAddress->validate();
        if ($billingAddressValidation !== true) {
            // get first validation message
            $billingAddressValidationError = reset($billingAddressValidation);
            $this->logger->error('Invalid Billing Address - ' . $billingAddressValidationError);
            throw new LocalizedException(__($billingAddressValidationError));
        }

        $source = [];
        // Get the source details
        if (!empty($primarySourceId)) {
            $source = $this->helper->getSourceDetails($primarySourceId);
            $readyForStorage = $this->checkoutSession->getDrReadyForStorage();
            $hasDrId = (bool)$this->digitalRiverCustomerIdManagement->getSessionDigitalRiverCustomerId();

            if ($source &&
                $source['message']['type'] == 'creditCard' &&
                $hasDrId &&
                $readyForStorage == "store"
            ) {
                $this->apiResult = $this->helper->setCustomerSource($primarySourceId);

                if (!$this->apiResult['success']
                    && $this->apiResult['statusCode'] === 409
                    && $this->apiResult['code'] === PlaceOrderResultInterface::CODE_ADDITIONAL_PAYMENT_ACTION_REQUIRED
                ) {
                    return $this->initFailureResult(
                        409,
                        PlaceOrderResultInterface::CODE_ADDITIONAL_PAYMENT_ACTION_REQUIRED
                    );
                }

                if (!$this->apiResult['success']) {
                    $this->logger->error('Unsuccessful result');
                    throw new LocalizedException(__('Unable to place order.'));
                }
                // unset the DrReadyForStorage session variable
                $this->checkoutSession->unsDrReadyForStorage();
            }
            $this->helper->assignSourceToCheckout($primarySourceId, $checkoutId);
            $this->checkoutSession->setIsDrPrimarySourceAssociatedWithCheckout(true);
        } elseif ($this->checkoutSession->getDrSourceId()) {
            // if this is an SCA after place order, get the source from the session
            $source = $this->helper->getSourceDetails($this->checkoutSession->getDrSourceId());
        } elseif (!empty($savedSourceId)) {
            // if checkout session is removed due to 2nd try of placing order (SCA)
            $source = $this->helper->getSourceDetails($savedSourceId);
        } else {
            $this->logger->critical("Primary source not used.");
        }

        $email = $billingAddress->getEmail();
        if ($email) {
            $data['email'] = $email;
        } elseif (isset($source['message']) &&
            isset($source['message']['owner']) &&
            isset($source['message']['owner']['email']) &&
            !empty($source['message']['owner']['email'])
        ) {
            $data['email'] = $source['message']['owner']['email'];
        }

        $purchaseOrderNumber = $quote->getPayment()->getPoNumber();
        if (!empty($purchaseOrderNumber)) {
            if (empty($data['metadata'])) {
                $data['metadata'] = [];
            }
            $data['metadata']['po_number'] = $purchaseOrderNumber;
        }

        if (!empty($data) && $updateCheckout) {
            $this->apiResult = $this->helper->setCheckoutUpdate($checkoutId, $data);
            if (!$this->apiResult['success']) {
                $this->logger->error('Unsuccessful result');
                throw new LocalizedException(__('Unable to place order.'));
            }
        }

        $quote->collectTotals();

        if (!$this->helper->isQuoteValid($quote)) {
            $this->logger->error('Magento Quote is Not Valid');
            throw new LocalizedException(__('Unable to place order.'));
        }

        $this->apiResult = $this->helper->setOrder($checkoutId, $upstreamId);

        if (!$this->apiResult['success']
            && $this->apiResult['statusCode'] === DrConnectorRepository::HTTP_CONFLICT
            && $this->apiResult['code'] === PlaceOrderResultInterface::CODE_ADDITIONAL_PAYMENT_ACTION_REQUIRED
        ) {
            return $this->resultBuilder->buildFailedResult(
                DrConnectorRepository::HTTP_CONFLICT,
                PlaceOrderResultInterface::CODE_ADDITIONAL_PAYMENT_ACTION_REQUIRED
            );
        }

        // force next checkout to be created, not updated
        $this->checkoutSession->unsSessionCheckSum();
        $this->checkoutSession->unsDrCheckoutBillingChecksum();
        $this->checkoutSession->unsDrCheckoutItemChecksum();

        if (!$this->apiResult['success']) {
            $this->logger->error('Unsuccessful result');
            throw new LocalizedException(__('Unable to place order.'));
        }
        $this->apiResult = $this->apiResult['message'];

        $billingAddress->setEmail($this->apiResult['email']);
        // result should be to return the billing address to Magento
        // "last successful quote"
        $this->checkoutSession->setLastQuoteId($cartId)->setLastSuccessQuoteId($cartId);
        if (!$quote->getCustomerId()) {
            $quote->setCustomerId(null)
                  ->setCustomerFirstname($billingAddress->getFirstname())
                  ->setCustomerLastname($billingAddress->getLastname())
                  ->setCustomerEmail($billingAddress->getEmail())
                  ->setCustomerIsGuest(true)
                  ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }

        if (!empty($source)) {
            $sourceType = $source['message']['type'];
        } else {
            $sourceType = 'customerCredit';
        }

        //DIRI-170 Set DR payment information before place order for Order Confirmation email
        $quote->getPayment()->setAdditionalInformation('method', $sourceType);

        $quote->getPayment()->setAdditionalInformation(
            'sourceNames',
            $this->sourceNameProviderPool->execute($quote)
        );

        $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quote]);

        try {
            $order = $this->quoteManagement->submit($quote);
        } catch (LocalizedException | \Exception $exception) {
            $this->logger->error('Magento Order Submission Error : ' .
                $this->jsonSerializer->serialize($exception->getLogMessage()));
            if (!empty($this->apiResult)) {
                $this->helper->setOrderCancellation($this->apiResult);
            }
            throw new LocalizedException(__($exception->getLogMessage()));
        }

        if ($order) {
            $this->checkoutSession->setLastOrderId($order->getId())
                                  ->setLastRealOrderId($order->getIncrementId())
                                  ->setLastOrderStatus($order->getStatus());
        } else {
            $this->logger->error('Order was not placed.');
            throw new LocalizedException(__('Unable to place order.'));
        }

        if (!empty($source)) {
            $this->logger->info($this->jsonSerializer->serialize($source));
            if ($source['success'] && isset($source['message']['wireTransfer'])) {
                $order->getPayment()->setAdditionalInformation($source['message']['wireTransfer']);
            }
        }

        $this->apiResult['dr_payment_method'] = ucwords($sourceType);
        $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
        $this->eventManager->dispatch(
            'dr_place_order_success',
            ['order' => $order, 'quote' => $quote, 'result' => $this->apiResult]
        );
        $orderId = (int)$order->getEntityId();
        $this->saveCharges($orderId, $this->apiResult);

        return $this->resultBuilder->buildSuccessResult($order, $this->apiResult);
    }

    /**
     * @return array
     */
    public function getLastApiResult(): array
    {
        return $this->apiResult;
    }

    /**
     * @param int $statusCode
     * @param string $fieldCode
     * @return PlaceOrderResultInterface
     */
    private function initFailureResult(int $statusCode, string $fieldCode): PlaceOrderResultInterface
    {
        /** @var PlaceOrderResultInterface $result */
        $result = $this->placeOrderResultFactory->create();
        $result->setStatus($statusCode);
        $result->setCode($fieldCode);
        $result->setOrderId(null);
        $result->setOrderIncrementId(null);

        return $result;
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    private function isQuoteActive(CartInterface $quote): bool
    {
        return (bool)$quote->getId() && $quote->getIsActive();
    }

    /**
     * Saves order charges in the dr_charge table
     *
     * @param int $orderId
     * @param array $result
     */
    private function saveCharges(int $orderId, array $result): void
    {
        if (!empty($result['payment'])) {
            $result['charges'] = $result['payment']['charges'] ?? '';
            $result['sources'] = $result['payment']['sources'] ?? '';
        }

        if (!empty($result['charges']) &&
            is_array($result['charges'])) {
            foreach ($result['charges'] as $charge) {
                if (!empty($charge["sourceId"])) {
                    $drChargeId = $charge["id"];
                    $drOrderId = $result["id"];
                    $drSourceId = $charge["sourceId"];
                    $drSourceType = "";

                    if (!empty($result['sources'])) {
                        foreach ($result['sources'] as $source) {
                            if ($source["id"] == $drSourceId) {
                                $drSourceType = $source["type"];
                            }
                        }
                    }

                    $amount = floatval($charge['amount']);

                    $this->chargeRepository->saveCharge(
                        $drChargeId,
                        $orderId,
                        $drOrderId,
                        $drSourceId,
                        $drSourceType,
                        $amount
                    );
                }
            }
        }
    }
}
