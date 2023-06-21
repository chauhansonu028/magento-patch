<?php
/**
 * Manual Cron Run Controller
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Block\Adminhtml\Cron\Edit\Process;
use Magento\Framework\View\Result\PageFactory;

/**
 * Catalog Sync Manual Action Controller
 */
class Manual extends Action implements HttpGetActionInterface
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
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var config
     */
    protected $config;

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
        $this->resultFactory = $context->getResultFactory();
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
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        
        if ($this->config->getIsEnabled()) {
            $resultPage->setActiveMenu('Digitalriver_DrPay::catalog_sync_grid')
                ->addBreadcrumb(__('Manual Sync'), __('Manual Sync'));
            $resultPage->getConfig()->getTitle()->prepend(__('Manual Sync Actions'));
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock(Process::class)
            );
        }

        return $resultPage;
    }
}
