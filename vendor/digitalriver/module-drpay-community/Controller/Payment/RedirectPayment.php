<?php
/**
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\Payment;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Helper\Data as ApiHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Reorder\Reorder;

class RedirectPayment implements HttpGetActionInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var Reorder
     */
    private $reorder;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    public function __construct(
        CheckoutSession $checkoutSession,
        Config $config,
        MessageManager $messageManager,
        OrderManagementInterface $orderManagement,
        Reorder $reorder,
        ResultFactory $resultFactory,
        ApiHelper $apiHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->orderManagement = $orderManagement;
        $this->reorder = $reorder;
        $this->resultFactory = $resultFactory;
        $this->apiHelper = $apiHelper;
    }

    /**
     * Canceled redirect payment transaction
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('checkout/cart');

        if ($this->config->getIsEnabled()) {
            try {
                $lastOrder = $this->checkoutSession->getLastRealOrder();
                $quote = $this->checkoutSession->getQuote();
                // Check that we have placed an order, that it is pending payment and that we don't already have a new
                // active cart
                if ($lastOrder->getIncrementId() && $lastOrder->getState() === Order::STATE_PENDING_PAYMENT
                    && !$quote->getIsActive()) {

                    $drOrderId = $lastOrder->getDrOrderId();
                    $drOrderDetails = $this->apiHelper->refreshOrder($drOrderId);
                    $drOrderState = $drOrderDetails['message']['state'] ?? '';
                    $drSessionState = $drOrderDetails['message']['payment']['session']['state'] ?? '';

                    // if order is in a successful state, redirect to thank you page else, redirect to cart
                    if (in_array($drOrderState, ['accepted', 'in_review']) ||
                        ($drOrderState === 'pending_payment' && $drSessionState === 'pending') ||
                        ($drOrderState === 'pending_payment' && $drSessionState === 'pending_funds')
                    ) {
                        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                            ->setPath('checkout/onepage/success');
                    } else {
                        // Re-add the items in the cart
                        $newCart = $this->reorder->execute(
                            $lastOrder->getIncrementId(),
                            (string)$lastOrder->getStoreId()
                        );

                        if ($newCart->getCart()->getId()) {
                            $this->checkoutSession->setQuoteId($newCart->getCart()->getId());
                        }

                        // Cancel the Magneto order
                        $this->orderManagement->cancel((int)$lastOrder->getEntityId());

                        // Notify the user
                        $this->messageManager->addWarningMessage(__(
                            'Your order (#%1) has been cancelled. Its content was re-added to your cart.',
                            $lastOrder->getIncrementId()
                        ));
                    }
                } else {
                    // The order wasn't placed yet
                    $this->messageManager->addWarningMessage(
                        __('Unable to process request.')
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Unable to process request.'));
            }
        }

        return $resultRedirect;
    }
}
