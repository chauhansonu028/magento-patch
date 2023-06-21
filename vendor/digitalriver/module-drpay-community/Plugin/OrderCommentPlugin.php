<?php

/**
 * Order Comment Plugin
 *
 * This plugin removes invalid refund amount when order comment is added
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @summary plugin to remove invalid refund amount in order comment
 */

namespace Digitalriver\DrPay\Plugin;

use Magento\Sales\Model\Order;

/**
 * Class Order Repository Get list.
 */
class OrderCommentPlugin
{
    const SEARCH_STRING_START = "We refunded";
    const SEARCH_STRING_END = "offline";
    const SEARCH_STRING_STORECREDIT = "Store Credit";

    /**
     * Add comment to status history before plugin
     *
     * @param Order $subject
     * @param string $comment
     * @param bool $status
     * @param bool $isVisibleOnFront
     * @return array
     */
    public function beforeAddCommentToStatusHistory(
        Order $subject,
        $comment,
        $status = false,
        $isVisibleOnFront = false
    ): array {
        if (strpos($comment, self::SEARCH_STRING_START) === 0 &&
            strpos($comment, self::SEARCH_STRING_END) !== 0 &&
            strpos($comment, self::SEARCH_STRING_END) !== false
        ) {
            return ["", $status, false];
        }
        if (strpos($comment, self::SEARCH_STRING_STORECREDIT) !== false) {
            return ["", $status, false];
        }
        return [$comment, $status, $isVisibleOnFront];
    }
}
