<?php
/**
 * Invoice details block class
 *
 * @summary provides invoice details from DR API
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Block\Sales\Order;

use Digitalriver\DrPay\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Invoice\Items;

class Invoice extends Items
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Invoice constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->helper = $helper;
    }

    /**
     * Function to return invoice file links from DR API
     *
     * @return array
     */
    public function getInvoiceLinks(): array
    {
        return $this->helper->getAllFiles($this->getOrder(), Data::INVOICE_FILE_TYPE);
    }
}
