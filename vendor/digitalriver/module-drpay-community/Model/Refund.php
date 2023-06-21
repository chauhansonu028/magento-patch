<?php declare(strict_types=1);
/**
 *  Refund model
 *
 * @summary Provides model for refunds
 * @author
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\Data\RefundInterface;
use Digitalriver\DrPay\Model\ResourceModel\Refund as RefundResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Data model of Refund Interface.
 */
class Refund extends AbstractModel implements RefundInterface
{
    /**
     * Initialize Validate model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(RefundResource::class);
    }

    /**
     * Retrieve entity id
     *
     * @return string
     */
    public function getId() : ?string
    {
        return $this->getData(self::DR_REFUND_ID);
    }

    /**
     * Set ID
     *
     * @param string $id
     *
     * @return RefundInterface
     */
    public function setId($id): RefundInterface
    {
        return $this->setData(self::DR_REFUND_ID, $id);
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return (float) $this->getData(self::AMOUNT);
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return RefundInterface
     */
    public function setAmount(float $amount): RefundInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }
}
