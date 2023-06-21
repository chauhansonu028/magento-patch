<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Block\Order;

use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProvider;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

abstract class AbstractAttachments extends Template
{
    /**
     * @var int
     */
    protected $productId;

    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var FileScopeDataProvider
     */
    protected $fileScopeDataProvider;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SessionQuote
     */
    private $sessionQuote;

    public function __construct(
        ConfigProvider $configProvider,
        FileScopeDataProvider $fileScopeDataProvider,
        Template\Context $context,
        Registry $registry,
        SessionQuote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->fileScopeDataProvider = $fileScopeDataProvider;
        $this->registry = $registry;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $this->productId = $this->getParentBlock()->getItem()->getProductId();
        $this->orderId = $this->getParentBlock()->getItem()->getOrderId();
        $this->storeId = $this->getParentBlock()->getItem()->getOrder()->getStoreId();
        $statusPass = empty($this->getOrderStatuses()) || in_array(
            $this->getParentBlock()->getItem()->getOrder()->getStatus(),
            $this->getOrderStatuses()
        );

        if (!$this->configProvider->isEnabled() || !$this->productId || !$statusPass) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface[]|bool
     */
    public function getAttachments()
    {
        $params = [
            RegistryConstants::PRODUCT => $this->productId,
            RegistryConstants::STORE => $this->storeId,
            RegistryConstants::EXTRA_URL_PARAMS => [
                'order' => $this->orderId,
                'product' => $this->productId
            ],
            RegistryConstants::INCLUDE_FILTER => $this->getAttachmentsFilter()
        ];

        if ($customerGroupId = $this->getCustomerGroup()) {
            $params[RegistryConstants::CUSTOMER_GROUP] = $customerGroupId;
        }

        return $this->fileScopeDataProvider->execute(
            $params,
            'frontendProduct'
        );
    }

    private function getCustomerGroup(): ?int
    {
        $customerGroupId = null;

        if ($order = $this->registry->registry('current_order')) {
            $customerGroupId = (int)$order->getCustomerGroupId();
        }

        if ($customerGroupId === null && $this->sessionQuote->hasCustomerId()) {
            $customerGroupId = (int)$this->sessionQuote->getQuote()->getCustomer()->getGroupId();
        }

        return $customerGroupId;
    }

    /**
     * @return int
     */
    abstract public function getAttachmentsFilter();

    /**
     * @return array
     */
    abstract public function getOrderStatuses();
}
