<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSyncAdmin\Controller\Adminhtml\Index;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\SaaSCommon\Model\ResyncManager;
use Magento\SaaSCommon\Model\ResyncManagerPool;

/**
 * Controller responsible for dealing with data re-sync requests from the react app.
 */
class ForceResync extends AbstractAction
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ResyncManagerPool
     */
    private $resyncManagerPool;

    /**
     * @var ResyncManager
     */
    private $productResync;

    /**
     * @param Context $context
     * @param ResyncManagerPool $resyncManagerPool
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ResyncManagerPool $resyncManagerPool,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resyncManagerPool = $resyncManagerPool;
        parent::__construct($context);
    }

    /**
     * Execute call to re-sync catalog product data
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();

        try {
            $this->resyncProducts();
            $this->resyncProductOverrides();
            $result = ['result' => 'Success'];
        } catch (\Exception $ex) {
            $result = ['result' => 'An error occurred during data re-sync.'];
        }

        return $jsonResult->setData($result);
    }

    /**
     * Check if user can access Catalog Sync
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_CatalogSyncAdmin::catalog_sync_admin');
    }

    /**
     * Resync products feed
     *
     * @throws \Exception
     */
    private function resyncProducts(): void
    {
        $this->productResync = $this->resyncManagerPool->getResyncManager('products');
        $this->productResync->resetSubmittedData();
    }

    /**
     * Resync product overrides feed
     *
     * @throws \Exception
     */
    private function resyncProductOverrides(): void
    {
        $this->productResync = $this->resyncManagerPool->getResyncManager('productoverrides');
        $this->productResync->resetSubmittedData();
    }
}
