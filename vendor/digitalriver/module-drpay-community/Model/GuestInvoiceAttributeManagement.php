<?php
/**
 * DR InvoiceAttribute Management Model
 *
 * @summary Provides methods for managing DR InvoiceAttributes on the backend for a non logged in user.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\InvoiceAttributeManagementInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestInvoiceAttributeManagement implements InvoiceAttributeManagementInterface
{
    const ERROR_CODE_CART_TYPE_NOT_VALID = 1;

    /**
     * Quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var InvoiceAttributeProcessor
     */
    protected $invoiceAttributeProcessor;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * InvoiceAttributeManagement constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param InvoiceAttributeProcessor $invoiceAttributeProcessor
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        InvoiceAttributeProcessor $invoiceAttributeProcessor,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->invoiceAttributeProcessor = $invoiceAttributeProcessor;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * Clears InvoiceAttributes from the quote
     *
     * @param string $cartId
     * @return bool|void
     * @throws InvalidArgumentException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function clearInvoiceAttribute($cartId)
    {
        if (!$cartId) {
            throw new InvalidArgumentException(
                __('Missing cartId argument'),
                null,
                self::ERROR_CODE_CART_TYPE_NOT_VALID
            );
        }

        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        return $this->invoiceAttributeProcessor->clearInvoiceAttributeId($quote);
    }

    /**
     * Assigns InvoiceAttribute to the DR Checkout
     *
     * @param string $cartId
     * @param \Digitalriver\DrPay\Api\InvoiceAttributeInterface[] $invoiceAttributes
     * @return bool|void
     * @throws LocalizedException
     * @throws \Digitalriver\DrPay\Exception\DrPayException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignInvoiceAttribute($cartId, $invoiceAttributes)
    {
        if (!$cartId) {
            throw new InvalidArgumentException(
                __('Missing cartId argument'),
                null,
                self::ERROR_CODE_CART_TYPE_NOT_VALID
            );
        }

        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        return $this->invoiceAttributeProcessor->assignInvoiceAttributeId($quote, $invoiceAttributes);
    }
}
