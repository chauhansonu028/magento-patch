<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Data;

use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PlaceOrderResult
 *
 * API result after order placement request
 */
class PlaceOrderResult implements PlaceOrderResultInterface
{
    /**
     * @var int
     */
    private $status;
    /**
     * @var string
     */
    private $code;
    /**
     * @var int|null
     */
    private $orderId;
    /**
     * @var string|null
     */
    private $orderIncrementId;
    /**
     * @var string|null
     */
    private $redirectURL;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * @param int|null $id
     */
    public function setOrderId(?int $id): void
    {
        $this->orderId = $id;
    }

    /**
     * @return string|null
     */
    public function getOrderIncrementId(): ?string
    {
        return $this->orderIncrementId;
    }

    /**
     * @param string|null $id
     */
    public function setOrderIncrementId(?string $id): void
    {
        $this->orderIncrementId = $id;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectURL;
    }

    /**
     * @param string|null $url
     */
    public function setRedirectUrl(?string $url): void
    {
        $this->redirectURL = $url;
    }
}
