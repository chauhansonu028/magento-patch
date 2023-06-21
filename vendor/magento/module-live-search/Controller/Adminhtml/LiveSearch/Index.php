<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LiveSearch\Controller\Adminhtml\LiveSearch;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Main page for Search Admin React application.
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_LiveSearch::livesearch';

    public const MENU_ID = 'Magento_LiveSearch::livesearch';

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritdoc
     *
     * @return ResultInterface|ResponseInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Live Search'));
        $this->setStoreView();
        return $resultPage;
    }

    /**
     * Set store view to store switcher
     *
     * @throws NoSuchEntityException
     */
    private function setStoreView(): void
    {
        $params = $this->getRequest()->getParams();
        $storeId = $params['store'] ?? $this->getRequest()->getParam('store');
        $store = $this->storeManager->getStore($storeId);
        $params['store'] = $store->getId();
        $this->getRequest()->setParams($params);
    }
}
