<?php
/**
 * Create DR Allocated Percent field
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Plugin\Model;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Model\DrOrderManagement;
use Digitalriver\DrPay\Model\SourceProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;

/**
 * Add reglatory fees to quote
 */
class QuoteManagementPlugin
{
    const PO_ORDER_METHOD_NAME = 'purchaseorder';
    /**
     * @var DrOrderManagement
     */
    private $drOrderManagement;

    /**
     * @var SourceProcessor
     */
    private $sourceProcessor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(
        DrOrderManagement $drOrderManagement,
        CartRepositoryInterface $cartRepository,
        SourceProcessor $sourceProcessor,
        Config $config
    ) {
        $this->drOrderManagement = $drOrderManagement;
        $this->cartRepository = $cartRepository;
        $this->sourceProcessor = $sourceProcessor;
        $this->config = $config;
    }

    /**
     * @param CartManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return int
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        \Closure $proceed,
        int $cartId,
        PaymentInterface $paymentMethod = null
    ): int {
        if (!$this->config->getIsEnabled()) {
            return $proceed($cartId, $paymentMethod);
        }
        /** @var string|null $currentMethod */
        $currentMethod = null;
        if ($paymentMethod) {
            $currentMethod = $paymentMethod->getMethod();
        } else {
            // Fallback to the quote's payment object
            try {
                $quote = $this->cartRepository->get($cartId);
                if ($quote instanceof Quote) {
                    $currentMethod = $quote->getPayment()->getMethod();
                }
            } catch (NoSuchEntityException $e) {
                // Do nothing, the current method will remain null
            }
        }

        if ($currentMethod !== self::PO_ORDER_METHOD_NAME) {
            return $proceed($cartId, $paymentMethod);
        }

        $this->sourceProcessor->lockCheckoutId();
        if (!$orderId = $this->drOrderManagement->placeOrder()) {
            throw new LocalizedException(
                __('A server error stopped your order from being placed. Please try to place your order again.')
            );
        }

        return $orderId;
    }
}
