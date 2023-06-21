<?php
/**
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Controller\Adminhtml\Sync;

use Digitalriver\DrPay\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Catalog Sync Grid Index
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Digitalriver_DrPay::catalog_sync_grid';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var config
     */
    private $config;

    /**
     * Details constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Config $config
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Catalog Sync list action
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        if ($this->config->getIsEnabled()) {
            $resultPage->setActiveMenu('Digitalriver_DrPay::catalog_sync_grid')
                ->addBreadcrumb(__('Catalog Sync'), __('Catalog Sync'));
            $resultPage->getConfig()->getTitle()->prepend(__('Catalog Sync Actions Log'));
        }
        return $resultPage;
    }
}
