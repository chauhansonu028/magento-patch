<?php
/**
 * Credit Memo details block class
 *
 * @summary provides credit memo details from DR API
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Block\Sales\Order;

use Digitalriver\DrPay\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Creditmemo\Items;

class CreditMemo extends Items
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * CreditMemo constructor.
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
     * Function to return credit memo file links from DR API
     *
     * @return array
     */
    public function getCreditMemoLinks(): array
    {
        return $this->helper->getAllFiles($this->getOrder(), Data::CREDIT_MEMO_FILE_TYPE);
    }
}
