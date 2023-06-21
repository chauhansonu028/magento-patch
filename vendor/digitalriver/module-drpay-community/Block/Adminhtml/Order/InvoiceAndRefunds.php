<?php
/**
 * Links for invoice and credit memo files in Digital River
 *
 * @summary Section to display invoice and CM files that are from DR
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Order;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Digitalriver\DrPay\Helper\Data;

/**
 * Adminhtml invoice or credit memo static message block
 */
class InvoiceAndRefunds extends AbstractOrder
{
    const INVOICE_GENERATED_MESSAGE = "Invoice Generated";
    const CREDIT_MEMO_GENERATED_MESSAGE = "Credit Memo Generated";

    /**
     * @var array $invoicesAndRefunds
     */
    private $invoicesAndRefunds;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * InvoiceAndRefunds constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param Data $helper
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        Data $helper,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        parent::__construct($context, $registry, $adminHelper, $data, $shippingHelper, $taxHelper);
        $this->helper = $helper;
    }

    /**
     * Returns the list of invoices for display
     *
     * @param Order $order
     * @return array
     */
    public function getInvoicesAndRefunds(Order $order): array
    {
        $this->invoicesAndRefunds = [];

        $invoiceDetails = $this->helper->getAllFiles($order, Data::INVOICE_FILE_TYPE);
        $refundDetails = $this->helper->getAllFiles($order, Data::CREDIT_MEMO_FILE_TYPE);

        $index = 0;
        $index = $this->formResponseData($invoiceDetails, $index, self::INVOICE_GENERATED_MESSAGE);
        $index = $this->formResponseData($refundDetails, $index, self::CREDIT_MEMO_GENERATED_MESSAGE);

        $this->sortFieldsBasedOnDate($index);

        return $this->invoicesAndRefunds;
    }

    /**
     * Forms the array to be returned
     *
     * @param array $details
     * @param int $index
     * @param string $message
     * @return int|mixed
     */
    private function formResponseData(array $details, int $index, string $message): int
    {
        $fileDetails = ($details) ? array_values($details) : [];
        foreach ($fileDetails as $fileInfo) {
            $this->invoicesAndRefunds[$index] = [
                "message" => $message,
                "link" => $fileInfo->getDrFileLinkUrl(),
                "date" => $fileInfo->getDrFileLinkCreatedAt()
            ];
            $index++;
        }
        return $index;
    }

    /**
     * Sorts the fields based on most recent first
     *
     * @param int $length
     */
    private function sortFieldsBasedOnDate(int $length)
    {
        for ($index = 0; $index < $length - 1; $index++) {
            for ($innerIndex = $index + 1; $innerIndex < $length; $innerIndex++) {
                if ($this->compareDates(
                    $this->invoicesAndRefunds[$index]["date"],
                    $this->invoicesAndRefunds[$innerIndex]["date"]
                ) < 0) {
                    $temp = $this->invoicesAndRefunds[$index];
                    $this->invoicesAndRefunds[$index] = $this->invoicesAndRefunds[$innerIndex];
                    $this->invoicesAndRefunds[$innerIndex] = $temp;
                }
            }
        }
    }

    /**
     * Compares 2 date string to find the older date
     *
     * @param string $date1
     * @param string $date2
     * @return false|int
     */
    private function compareDates(string $date1, string $date2)
    {
        return strtotime($date1) - strtotime($date2);
    }
}
