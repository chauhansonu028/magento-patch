<?php
/**
 * DR Sources management model
 *
 * @summary Provides methods for managing DR sources on the backend for a non-logged user.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\SourceManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestSourceManagement implements SourceManagementInterface
{
    const ERROR_CODE_MISSING_CART_ID = 1;

    /**
     * @var SourceProcessor
     */
    protected $sourceProcessor;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * SourceManagement constructor.
     *
     * @param SourceProcessor $sourceProcessor
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        SourceProcessor $sourceProcessor,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->sourceProcessor = $sourceProcessor;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * Locks DR checkout Id
     *
     * @param string $cartId
     * @return bool
     */
    public function lockCheckoutId($cartId)
    {
        if (!$cartId) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('Missing cartId argument'),
                null,
                self::ERROR_CODE_MISSING_CART_ID
            );
        }

        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        if ($quoteIdMask->getQuoteId() == null) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(
                __('Invalid cartId'),
                null,
                self::ERROR_CODE_MISSING_CART_ID
            );
        }

        return $this->sourceProcessor->lockCheckoutId();
    }
}
