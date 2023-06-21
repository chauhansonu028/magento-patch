<?php
/**
 * DR InvoiceAttribute Management Model
 *
 * @summary Provides methods for managing DR InvoiceAttributes on the backend for a logged in user.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\InvoiceAttributeManagementInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;

class InvoiceAttributeManagement implements InvoiceAttributeManagementInterface
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
     * InvoiceAttributeManagement constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param InvoiceAttributeProcessor $invoiceAttributeProcessor
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        InvoiceAttributeProcessor $invoiceAttributeProcessor
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->invoiceAttributeProcessor = $invoiceAttributeProcessor;
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

        $quote = $this->quoteRepository->getActive($cartId);
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

        $quote = $this->quoteRepository->getActive($cartId);
        $this->invoiceAttributeProcessor->assignInvoiceAttributeId($quote, $invoiceAttributes);
    }
}
