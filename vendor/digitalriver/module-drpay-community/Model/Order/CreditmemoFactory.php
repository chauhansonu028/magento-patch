<?php
/**
 * CreditmemoFactory for Duty fee and IOR tax.
 * This preference is used to add duty fee and IOR tax to credit memo section during total updates
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model\Order;

use Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;

/**
 * Class Creditmemo Factory Prefernce
 */
class CreditmemoFactory extends \Magento\Sales\Model\Order\CreditmemoFactory
{
    /**
     * Order convert object.
     *
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $convertor;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Digitalriver\DrPay\Helper\Config
     */
    private $config;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Digitalriver\DrPay\Helper\Config $config,
        JsonSerializer $serializer = null,
        FormatInterface $localeFormat = null
    ) {
        $this->convertor = $convertOrderFactory->create();
        $this->taxConfig = $taxConfig;
        $this->config = $config;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(JsonSerializer::class);
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(FormatInterface::class);
    }

    /**
     * Prepare order creditmemo based on order items and requested params
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     * @return Creditmemo
     */
    public function createByOrder(\Magento\Sales\Model\Order $order, array $data = [])
    {
        $totalQty = 0;
        $creditmemo = $this->convertor->toCreditmemo($order);

        $qtyList = isset($data['qtys']) ? $data['qtys'] : [];

        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canRefundItem($orderItem, $qtyList)) {
                continue;
            }

            $item = $this->convertor->itemToCreditmemoItem($orderItem);
            $qty = $this->getQtyToRefund($orderItem, $qtyList);
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->initData($creditmemo, $data);

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Prepare order creditmemo based on invoice and requested params
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param array $data
     * @return Creditmemo
     */
    public function createByInvoice(\Magento\Sales\Model\Order\Invoice $invoice, array $data = [])
    {
        $order = $invoice->getOrder();
        $totalQty = 0;
        $qtyList = isset($data['qtys']) ? $data['qtys'] : [];
        $creditmemo = $this->convertor->toCreditmemo($order);
        $creditmemo->setInvoice($invoice);

        $invoiceRefundLimitsQtyList = $this->getInvoiceRefundLimitsQtyList($invoice);

        foreach ($invoice->getAllItems() as $invoiceItem) {
            /** @var OrderItemInterface $orderItem */
            $orderItem = $invoiceItem->getOrderItem();

            if (!$this->canRefundItem($orderItem, $qtyList, $invoiceRefundLimitsQtyList)) {
                continue;
            }

            $qty = min(
                $this->getQtyToRefund($orderItem, $qtyList, $invoiceRefundLimitsQtyList),
                $invoiceItem->getQty()
            );
            $totalQty += $qty;
            $item = $this->convertor->itemToCreditmemoItem($orderItem);
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->initData($creditmemo, $data);
        if (!isset($data['shipping_amount'])) {
            $baseAllowedAmount = $this->getShippingAmount($invoice);
            $creditmemo->setBaseShippingAmount($baseAllowedAmount);
        }

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Check if order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $qtys
     * @param array $invoiceQtysRefundLimits
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function canRefundItem($item, $qtys = [], $invoiceQtysRefundLimits = [])
    {
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($this->canRefundNoDummyItem($child, $invoiceQtysRefundLimits)) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $this->canRefundNoDummyItem($parent, $invoiceQtysRefundLimits);
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
            return false;
        } else {
            return $this->canRefundNoDummyItem($item, $invoiceQtysRefundLimits);
        }
    }

    /**
     * Check if no dummy order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function canRefundNoDummyItem($item, $invoiceQtysRefundLimits = [])
    {
        if ($item->getQtyToRefund() < 0) {
            return false;
        }
        if (isset($invoiceQtysRefundLimits[$item->getId()])) {
            return $invoiceQtysRefundLimits[$item->getId()] > 0;
        }
        return true;
    }

    /**
     * Initialize creditmemo state based on requested parameters
     *
     * @param Creditmemo $creditmemo
     * @param array $data
     * @return void
     */
    protected function initData($creditmemo, $data)
    {
        if (isset($data['shipping_amount'])) {
            $shippingAmount = $this->config->convertToBaseCurrency($this->parseNumber($data['shipping_amount']));
            $creditmemo->setBaseShippingAmount($shippingAmount);
            $creditmemo->setBaseShippingInclTax($shippingAmount);
        }
        if (isset($data['adjustment_positive'])) {
            $adjustmentPositiveAmount = $this->parseAdjustmentAmount($data['adjustment_positive']);
            $creditmemo->setAdjustmentPositive($adjustmentPositiveAmount);
        }
        if (isset($data['adjustment_negative'])) {
            $adjustmentNegativeAmount = $this->parseAdjustmentAmount($data['adjustment_negative']);
            $creditmemo->setAdjustmentNegative($adjustmentNegativeAmount);
        }
        if (isset($data['dr_duty_fee'])) {
            $dutyFeeAmount = $this->config->convertToBaseCurrency($this->parseAdjustmentAmount($data['dr_duty_fee']));
            $creditmemo->setBaseDrDutyFee($dutyFeeAmount);
        }
        if (isset($data['dr_ior_tax'])) {
            $iorTax = $this->config->convertToBaseCurrency($this->parseAdjustmentAmount($data['dr_ior_tax']));
            $creditmemo->setBaseDrIorTax($iorTax);
        }
    }

    /**
     * Calculate product options.
     *
     * @param Item $orderItem
     * @param int $parentQty
     * @return int
     */
    private function calculateProductOptions(Item $orderItem, int $parentQty): int
    {
        $qty = $parentQty;
        $productOptions = $orderItem->getProductOptions();
        if (isset($productOptions['bundle_selection_attributes'])) {
            $bundleSelectionAttributes = $this->serializer->unserialize(
                $productOptions['bundle_selection_attributes']
            );
            if ($bundleSelectionAttributes) {
                $qty = $bundleSelectionAttributes['qty'] * $parentQty;
            }
        }
        return $qty;
    }

    /**
     * Gets list of quantities based on invoice refunded items.
     *
     * @param Invoice $invoice
     * @return array
     */
    private function getInvoiceRefundedQtyList(Invoice $invoice): array
    {
        $invoiceRefundedQtyList = [];
        foreach ($invoice->getOrder()->getCreditmemosCollection() as $creditmemo) {
            if ($creditmemo->getState() !== Creditmemo::STATE_CANCELED &&
                $creditmemo->getInvoiceId() === $invoice->getId()
            ) {
                foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                    $orderItemId = $creditmemoItem->getOrderItem()->getId();
                    if (isset($invoiceRefundedQtyList[$orderItemId])) {
                        $invoiceRefundedQtyList[$orderItemId] += $creditmemoItem->getQty();
                    } else {
                        $invoiceRefundedQtyList[$orderItemId] = $creditmemoItem->getQty();
                    }
                }
            }
        }

        return $invoiceRefundedQtyList;
    }

    /**
     * Gets limits of refund based on invoice items.
     *
     * @param Invoice $invoice
     * @return array
     */
    private function getInvoiceRefundLimitsQtyList(Invoice $invoice): array
    {
        $invoiceRefundLimitsQtyList = [];
        $invoiceRefundedQtyList = $this->getInvoiceRefundedQtyList($invoice);

        foreach ($invoice->getAllItems() as $invoiceItem) {
            $qtyCanBeRefunded = $invoiceItem->getQty();
            $orderItemId = $invoiceItem->getOrderItem()->getId();
            if (isset($invoiceRefundedQtyList[$orderItemId])) {
                $qtyCanBeRefunded = $qtyCanBeRefunded - $invoiceRefundedQtyList[$orderItemId];
            }
            $invoiceRefundLimitsQtyList[$orderItemId] = $qtyCanBeRefunded;
        }

        return $invoiceRefundLimitsQtyList;
    }

    /**
     * Gets quantity of items to refund based on order item.
     *
     * @param Item $orderItem
     * @param array $qtyList
     * @param array $refundLimits
     * @return float
     */
    private function getQtyToRefund(Item $orderItem, array $qtyList, array $refundLimits = []): float
    {
        $qty = 0;
        if ($orderItem->isDummy()) {
            if (isset($qtyList[$orderItem->getParentItemId()])) {
                $parentQty = $qtyList[$orderItem->getParentItemId()];
            } elseif ($orderItem->getProductType() === BundlePrice::PRODUCT_TYPE) {
                $parentQty = $orderItem->getQtyInvoiced();
            } else {
                $parentQty = $orderItem->getParentItem() ? $orderItem->getParentItem()->getQtyToRefund() : 1;
            }
            $qty = $this->calculateProductOptions($orderItem, $parentQty);
        } else {
            if (isset($qtyList[$orderItem->getId()])) {
                $qty = $qtyList[$orderItem->getId()];
            } elseif (!count($qtyList)) {
                $qty = $orderItem->getQtyToRefund();
            } else {
                return (float)$qty;
            }

            if (isset($refundLimits[$orderItem->getId()])) {
                $qty = min($qty, $refundLimits[$orderItem->getId()]);
            }
        }

        return (float)$qty;
    }

    /**
     * Gets shipping amount based on invoice.
     *
     * @param Invoice $invoice
     * @return float
     */
    private function getShippingAmount(Invoice $invoice): float
    {
        $order = $invoice->getOrder();
        $isShippingInclTax = $this->taxConfig->displaySalesShippingInclTax($order->getStoreId());
        if ($isShippingInclTax) {
            $amount = $order->getBaseShippingInclTax() -
                $order->getBaseShippingRefunded() -
                $order->getBaseShippingTaxRefunded();
        } else {
            $amount = $order->getBaseShippingAmount() - $order->getBaseShippingRefunded();
            $amount = min($amount, $invoice->getBaseShippingAmount());
        }

        return (float)$amount;
    }

    /**
     * Parse adjustment amount value to number
     *
     * @param string|null $amount
     *
     * @return float|null
     */
    private function parseAdjustmentAmount($amount)
    {
        $amount = trim($amount);
        $percentAmount = substr($amount, -1) == '%';
        $amount = $this->parseNumber($amount);

        return $percentAmount ? $amount . '%' : $amount;
    }

    /**
     * Parse value to number
     *
     * @param string|null $value
     *
     * @return float|null
     */
    private function parseNumber($value)
    {
        return $this->localeFormat->getNumber($value);
    }
}
