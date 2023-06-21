<?php
/**
 * DR Sources management model
 *
 * @summary Provides methods for managing DR sources on the backend for a logged in user.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\SourceManagementInterface;

class SourceManagement implements SourceManagementInterface
{
    const ERROR_CODE_MISSING_CART_ID = 1;

    /**
     * @var SourceProcessor
     */
    protected $sourceProcessor;

    /**
     * SourceManagement constructor.
     *
     * @param SourceProcessor $sourceProcessor
     */
    public function __construct(
        SourceProcessor $sourceProcessor
    ) {
        $this->sourceProcessor = $sourceProcessor;
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

        return $this->sourceProcessor->lockCheckoutId();
    }
}
