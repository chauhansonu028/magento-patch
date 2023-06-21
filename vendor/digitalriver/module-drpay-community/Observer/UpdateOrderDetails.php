<?php
/**
 * DrPay Observer
 */

namespace Digitalriver\DrPay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as Order;

/**
 * Update Digitalriver order details
 *
 */
class UpdateOrderDetails implements ObserverInterface
{
    const ORDER_ACCEPTED = 'accepted';
    const ORDER_IN_REVIEW = 'in_review';

    /**
     * @var \Digitalriver\DrPay\Helper\Data
     */
    private $helper;

    /**
     * @var \Digitalriver\DrPay\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var \Digitalriver\DrPay\Model\DrConnector
     */
    private $drConnector;

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    public function __construct(
        \Digitalriver\DrPay\Helper\Data $helper,
        \Digitalriver\DrPay\Helper\Config $config,
        \Magento\Checkout\Model\Session $session,
        \Magento\Sales\Model\Order $order,
        \Digitalriver\DrPay\Model\DrConnector $drConnector,
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->helper = $helper;
        $this->config = $config;
        $this->session = $session;
        $this->order = $order;
        $this->drConnector = $drConnector;
        $this->_logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $result = $observer->getEvent()->getResult();

        if (isset($result["id"])) {
            $orderId = $result["id"];
            $order->setDrOrderId($orderId);
            $order->setDrApiType('drapi');
            $order->setDrPaymentMethod($result['dr_payment_method']);

            $drTotalTax = $result['totalTax'];
            $order->setDrTax($drTotalTax);
            $order->setTaxAmount($drTotalTax);
            $order->setBaseTaxAmount($this->config->convertToBaseCurrency($drTotalTax));

            $subTotalExclTax = 0;
            $subTotalInclTax = 0;
            $subTotalTax = 0;

            if (isset($result['items'])) {
                $lineItems = $result['items'];
                $model = $this->drConnector->load($orderId, 'requisition_id');
                $model->setRequisitionId($orderId);
                $lineItemIds = [];
                foreach ($lineItems as $item) {
                    $lineItemIds[] = ['qty' => $item['quantity'],'lineitemid' => $item['id'], 'sku' => $item['skuId']];
                }
                $model->setLineItemIds($this->jsonSerializer->serialize($lineItemIds));
                $model->save();

                foreach ($order->getAllItems() as $orderitem) {
                    // get the magento lineitem quote ID
                    $magentoItemId = $orderitem->getQuoteItemId();

                    foreach ($lineItems as $item) {
                        // loop thru the item's custom attributes to extract the magento_quote_item_id,
                        // productPriceExclTax, productPriceSubTotalInclTax, productPriceSubTotalExclTax

                        $drItemMagentoRefId = $item['metadata']['magento_quote_item_id'];
                        $metadata = $item['metadata'];
                        unset($item['metadata']);
                        $item = array_merge($item, $metadata);// phpcs:ignore Magento2.Performance.ForeachArrayMerge

                        // if the DR cart's magento_quote_item_id == magentoItemId, then update the Items details
                        if ($drItemMagentoRefId == $magentoItemId) {
                            $this->updateDrItemsDetails($orderitem, $item);

                            $subTotalExclTax += $orderitem->getRowTotal();
                            $subTotalInclTax += $orderitem->getRowTotalInclTax();
                            $subTotalTax += $orderitem->getTaxAmount();
                            break;
                        }
                    }
                }
            }

            // update bundle parent product amounts
            $this->updateParentItemTotals($order);

            // required for MOM
            $shippingTax = (isset($result['shippingChoice'])) ? $result['shippingChoice']['taxAmount'] : 0;
            $shippingTotalExclTax = (isset($result['shippingChoice'])) ? $result['shippingChoice']['amount'] : 0;
            $shippingTotalInclTax = $shippingTotalExclTax + $shippingTax;

            if (isset($result['metadata']['shippingDiscount']) && $result['metadata']['shippingDiscount'] > 0) {
                $shippingTotalExclTax += $result['metadata']['shippingDiscount'];
                $shippingTotalInclTax += $result['metadata']['shippingDiscount'];
            }

            $order->setShippingTaxAmount($this->config->round($shippingTax));
            $order->setBaseShippingTaxAmount($this->config->convertToBaseCurrency($order->getShippingTaxAmount()));

            $order->setShippingAmount($this->config->round($shippingTotalExclTax));
            $order->setBaseShippingAmount($this->config->convertToBaseCurrency($order->getShippingAmount()));

            $order->setShippingInclTax($this->config->round($shippingTotalInclTax));
            $order->setBaseShippingInclTax($this->config->convertToBaseCurrency($order->getShippingInclTax()));

            $order->setSubtotal($this->config->round($subTotalExclTax));
            $order->setBaseSubtotal($this->config->convertToBaseCurrency($order->getSubtotal()));
            $order->setSubtotalInclTax($this->config->round($subTotalInclTax));
            $order->setBaseSubtotalInclTax($this->config->convertToBaseCurrency($order->getSubtotalInclTax()));

            // clears out compensation amounts Magento set based on tax settings
            $order->setDiscountTaxCompensationAmount(0);
            $order->setBaseDiscountTaxCompensationAmount(0);
            $order->setShippingDiscountTaxCompensationAmount(0);
            $order->setBaseShippingDiscountTaxCompensationAmount(0);
            $order->setBaseWeeeTaxDisposition(0);
            $order->setWeeeTaxDisposition(0);
            $order->setWeeeTaxRowDisposition(0);
            $order->setBaseWeeeTaxRowDisposition(0);

            // Set the order status based on the DR order response:
            $order->setDrOrderState($result['state']);
            $state = Order::STATE_PENDING_PAYMENT;
            $comment = 'Pending Payment';
            switch ($result['state']) {
                case self::ORDER_ACCEPTED:
                    $state = Order::STATE_PROCESSING;
                    $comment = 'Order accepted';
                    break;
                case self::ORDER_IN_REVIEW:
                    $state = Order::STATE_PAYMENT_REVIEW;
                    $comment = 'Payment Review';
                    break;
            }
            $order->addStatusHistoryComment(__($comment));
            $order->setState($state);
            $order->setStatus($state);
            $order->save();
            $this->config->clearSessionData();
        }
    }

    public function updateDrItemsDetails($orderitem, $item)
    {
        $orderitem->setDrOrderLineitemId($item['id']);

        # add fees tax to the product tax amount
        $feeTax = $item['fees']['taxAmount'] ?? 0;
        $orderitem->setTaxAmount($this->config->round($item['tax']['amount'] + $feeTax));

        $orderitem->setBaseTaxAmount($this->config->convertToBaseCurrency($orderitem->getTaxAmount()));
        $orderitem->setTaxPercent($item['tax']['rate'] * 100);

        $subTotalInclTax = $item['amount'] + $item['tax']['amount'];
        $subTotalExclTax = $item['amount'];

        // need to add the discounts back in so since magento will subtract it in the admin
        if ($item['productDiscount'] > 0) {
            $subTotalInclTax += $item['productDiscount'];
            $subTotalExclTax += $item['productDiscount'];
        }

        $productPriceInclTax = $subTotalInclTax / $item['quantity'];
        $productPriceExclTax = $subTotalExclTax / $item['quantity'];

        $taxInclusive = $this->config->isTaxInclusive();
        if ($taxInclusive) {
            $orderitem->setOriginalPrice($this->config->round($productPriceInclTax));
        } else {
            $orderitem->setOriginalPrice($this->config->round($productPriceExclTax));
        }
        $orderitem->setBaseOriginalPrice($this->config->convertToBaseCurrency($orderitem->getOriginalPrice()));

        $orderitem->setPrice($this->config->round($productPriceExclTax));
        $orderitem->setBasePrice($this->config->convertToBaseCurrency($orderitem->getPrice()));

        $orderitem->setPriceInclTax($this->config->round($productPriceInclTax));
        $orderitem->setBasePriceInclTax($this->config->convertToBaseCurrency($orderitem->getPriceInclTax()));

        $orderitem->setRowTotal($this->config->round($subTotalExclTax));
        $orderitem->setBaseRowTotal($this->config->convertToBaseCurrency($orderitem->getRowTotal()));

        $orderitem->setRowTotalInclTax($this->config->round($subTotalInclTax));
        $orderitem->setBaseRowTotalInclTax($this->config->convertToBaseCurrency($orderitem->getRowTotalInclTax()));

        $orderitem->setDiscountTaxCompensationAmount(0);
        $orderitem->setBaseDiscountTaxCompensationAmount(0);

        $orderitem->setBaseWeeeTaxDisposition(0);
        $orderitem->setWeeeTaxDisposition(0);
        $orderitem->setWeeeTaxRowDisposition(0);
        $orderitem->setBaseWeeeTaxRowDisposition(0);
    }

    /**
     * Update amounts for bundle parent products
     * @param $order
     */
    public function updateParentItemTotals($order)
    {
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                $price = 0;
                $basePrice = 0;
                $priceInclTax = 0;
                $basePriceInclTax = 0;
                $rowTotal = 0;
                $baseRowTotal = 0;
                $rowTotalInclTax = 0;
                $baseRowTotalInclTax = 0;
                $taxAmount = 0;
                $baseTaxAmount = 0;
                $taxPercent = 0;

                $weeeTaxAppliedAmount = 0;
                $weeeTaxAppliedRowAmount = 0;
                $baseWeeeTaxAppliedAmount = 0;
                $baseWeeeTaxAppliedRowAmnt = 0;

                foreach ($orderItem->getChildrenItems() as $childItem) {
                    $price += $childItem->getPrice();
                    $basePrice += $childItem->getBasePrice();
                    $priceInclTax += $childItem->getPriceInclTax();
                    $basePriceInclTax += $childItem->getBasePriceInclTax();
                    $rowTotal += $childItem->getRowTotal();
                    $baseRowTotal += $childItem->getBaseRowTotal();
                    $rowTotalInclTax += $childItem->getRowTotalInclTax();
                    $baseRowTotalInclTax += $childItem->getBaseRowTotalInclTax();
                    $taxAmount += $childItem->getTaxAmount();
                    $baseTaxAmount += $childItem->getBaseTaxAmount();
                    $weeeTaxAppliedAmount += $childItem->getWeeeTaxAppliedAmount();
                    $weeeTaxAppliedRowAmount += $childItem->getWeeeTaxAppliedRowAmount();
                    $baseWeeeTaxAppliedAmount += $childItem->getBaseWeeeTaxAppliedAmount();
                    $baseWeeeTaxAppliedRowAmnt += $childItem->getBaseWeeeTaxAppliedRowAmnt();
                    $taxPercent = $childItem->getTaxPercent(); // assume the percent is the same
                }

                $orderItem->setPrice($price);
                $orderItem->setBasePrice($basePrice);
                $orderItem->setPriceInclTax($priceInclTax);
                $orderItem->setBasePriceInclTax($basePriceInclTax);
                $orderItem->setRowTotal($rowTotal);
                $orderItem->setBaseRowTotal($baseRowTotal);
                $orderItem->setRowTotalInclTax($rowTotalInclTax);
                $orderItem->setBaseRowTotalInclTax($baseRowTotalInclTax);
                $orderItem->setTaxAmount($taxAmount);
                $orderItem->setBaseTaxAmount($baseTaxAmount);
                $orderItem->setTaxPercent($taxPercent);
                $orderItem->setDiscountTaxCompensationAmount(0);
                $orderItem->setBaseDiscountTaxCompensationAmount(0);

                $orderItem->setBaseWeeeTaxDisposition(0);
                $orderItem->setWeeeTaxDisposition(0);
                $orderItem->setWeeeTaxRowDisposition(0);
                $orderItem->setBaseWeeeTaxRowDisposition(0);

                // parent products should not have fee information
                $orderItem->setWeeeTaxAppliedAmount(0);
                $orderItem->setWeeeTaxAppliedRowAmount(0);
                $orderItem->setBaseWeeeTaxAppliedAmount(0);
                $orderItem->setBaseWeeeTaxAppliedRowAmnt(0);

                // if a static bundle, let's treat it like a dynamic bundle in the admin
                // the product_calculations flag determines appearance in admin and will show products individually
                $productOptions = $orderItem->getProductOptions();
                if (isset($productOptions['product_calculations']) && $productOptions['product_calculations'] == 1) {
                    $productOptions['product_calculations'] = 0;
                    $orderItem->setProductOptions($productOptions);
                }
            }
        }
    }
}
