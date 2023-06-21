<?php

namespace Digitalriver\DrPay\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Tax\Block\Item\Price\Renderer;
use Digitalriver\DrPay\Helper\Config;

/**
 * Plugin to add wee taxes to subtotal
 */
class CartPlugin
{
    /** @var Session  */
    private $checkoutSession;

    /** @var Data  */
    private $checkoutHelper;

    /** @var Renderer  */
    private $itemPriceRenderer;

    /** @var Config  */
    private $config;

    /**
     * @param Session $checkoutSession
     * @param Data $checkoutHelper
     * @param Renderer $itemPriceRenderer
     */
    public function __construct(
        Session $checkoutSession,
        Data $checkoutHelper,
        Renderer $itemPriceRenderer,
        Config $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
        $this->config = $config;
    }

    /**
     * @param Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Cart $subject, $result)
    {
        $quote = $this->getQuote();
        if (!isset($result['items']) ||
            0 === count($result['items']) ||
            !$this->config->getIsEnabled($quote->getStoreId())
        ) {
            return $result;
        }

        // create an array of skus in the cart
        $totalFees = 0;
        foreach ($quote->getAllItems() as $item) {
            $itemRowFeeAmount = $item->getWeeeTaxAppliedRowAmount();
            if (empty($itemRowFeeAmount)) {
                continue;
            }

            $totalFees += $itemRowFeeAmount;
        }
        $result['subtotal_incl_tax'] = $this->checkoutHelper->formatPrice($totalFees + $result['subtotalAmount']);

        return $result;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
