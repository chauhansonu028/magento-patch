<?php
/**
 * DR Tax ID Management Model
 *
 * @summary Provides methods for managing DR Tax IDs on the backend for a logged in user.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\TaxIdManagementInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;

class TaxIdManagement implements TaxIdManagementInterface
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
     * TaxIdManagement constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param TaxIdProcessor $taxIdProcessor
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        TaxIdProcessor $taxIdProcessor
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->taxIdProcessor = $taxIdProcessor;
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

        $quote = $this->quoteRepository->getActive($cartId);
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

        $quote = $this->quoteRepository->getActive($cartId);
        $this->taxIdProcessor->assignTaxIds($quote, $customerType, $taxIdentifiers);
    }
}
