<?php
/**
 * DR Tax ID Management Model
 *
 * @summary Provides methods for managing DR Tax IDs on the backend for a non logged in user.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\TaxIdManagementInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestTaxIdManagement implements TaxIdManagementInterface
{
    const ERROR_CODE_CART_TYPE_NOT_VALID = 1;

    /**
     * Quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var TaxIdProcessor
     */
    protected $taxIdProcessor;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * TaxIdManagement constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param TaxIdProcessor $taxIdProcessor
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        TaxIdProcessor $taxIdProcessor,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->taxIdProcessor = $taxIdProcessor;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * Clears TaxIds from the quote
     *
     * @param string $cartId
     * @return bool|void
     * @throws InvalidArgumentException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function clearTaxIds($cartId)
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
        return $this->taxIdProcessor->clearTaxIds($quote);
    }

    /**
     * Assigns Tax ID to the DR Checkout
     *
     * @param string $cartId
     * @param string $customerType
     * @param \Digitalriver\DrPay\Api\TaxIdInterface[] $taxIdentifiers
     * @return bool|void
     * @throws LocalizedException
     * @throws \Digitalriver\DrPay\Exception\DrPayException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignTaxIds($cartId, $customerType, $taxIdentifiers)
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

        return $this->taxIdProcessor->assignTaxIds($quote, $customerType, $taxIdentifiers);
    }
}
