<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductRecommendationsSyncAdmin\Controller\Adminhtml\Index;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\CatalogSyncAdmin\Model\ServiceClientInterface;

/**
 * Controller responsible for dealing with the requests from the react app.
 */
class Middleware extends AbstractAction
{
    /**
     * Config paths
     */
    const BASE_ROUTE_CONFIG_PATH = 'product_recommendations_sync_admin/admin_api_path';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ServiceClientInterface
     */
    private $serviceClient;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param Context $context
     * @param ServiceClientInterface $serviceClient
     * @param ScopeConfigInterface $config
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ServiceClientInterface $serviceClient,
        ScopeConfigInterface $config,
        JsonFactory $resultJsonFactory
    ) {
        $this->serviceClient = $serviceClient;
        $this->config = $config;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute middleware call
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();
        $method = $this->getRequest()->getParam('method', 'GET');
        $version = $this->getRequest()->getParam('version', 'v1');
        $uri = $this->getRequest()->getParam('uri','');
        $payload = $this->getRequest()->getParam('payload', '');
        $baseRoute = $this->config->getValue(self::BASE_ROUTE_CONFIG_PATH);
        $url = $this->serviceClient->getUrl($baseRoute, $version, $uri);

        $result = [
            "result" => $this->serviceClient->request($method, $url, $payload),
            "uri" => $uri
        ];

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
}
