<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface PlaceOrderResultBuilderInterface
 *
 * Builds PlaceOrderResultInterface
 */
interface PlaceOrderResultBuilderInterface
{
    /**
     * @param OrderInterface $order
     * @param array $apiResult
     * @return PlaceOrderResultInterface
     */
    public function buildSuccessResult(OrderInterface $order, array $apiResult): PlaceOrderResultInterface;

    /**
     * @param int $statusCode
     * @param string $fieldCode
     * @return PlaceOrderResultInterface
     */
    public function buildFailedResult(int $statusCode, string $fieldCode): PlaceOrderResultInterface;
}
