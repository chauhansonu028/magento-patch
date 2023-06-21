<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api\Data;

/**
 * Interface PlaceOrderResultInterface
 *
 * Interface for API result after order placement request
 */
interface PlaceOrderResultInterface
{
    public const FIELD_STATUS = 'status';
    public const FIELD_REDIRECT_URL = 'redirect_url';
    public const FIELD_CODE = 'code';
    public const FIELD_ORDER_INCREMENT_ID = 'order_increment_id';
    public const FIELD_ORDER_ID = 'order_id';
    public const CODE_OK = 'OK';
    public const CODE_ADDITIONAL_PAYMENT_ACTION_REQUIRED = 'additional_payment_action_required';

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * @param int|null $id
     * @return void
     */
    public function setOrderId(?int $id): void;

    /**
     * @return string|null
     */
    public function getOrderIncrementId(): ?string;

    /**
     * @param string|null $id
     * @return void
     */
    public function setOrderIncrementId(?string $id): void;

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string;

    /**
     * @param string|null $url
     * @return void
     */
    public function setRedirectUrl(?string $url): void;
}
