<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\StoredMethods;

use Digitalriver\DrPay\Model\StoredMethods\ConfigProvider;
use Digitalriver\DrPay\Helper\Config;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index implements AccountInterface
{
    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    private $view;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ConfigProvider $configProvider,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $eventManager,
        Config $config
    ) {
        $this->view = $context->getView();
        $this->pageFactory = $pageFactory;
        $this->configProvider = $configProvider;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->eventManager = $eventManager;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->config->getIsEnabled() || !$this->configProvider->isEnabled()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/index');
            return $resultRedirect;
        }

        $this->eventManager->dispatch('customer_account_stored_methods_page_visit');

        return $this->pageFactory->create();
    }
}
