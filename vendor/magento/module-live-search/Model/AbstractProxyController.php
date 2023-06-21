<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Model;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\LiveSearch\Api\ApiException;
use Magento\LiveSearch\Api\KeyInvalidException;
use Magento\LiveSearch\Api\ServiceClientInterface;

/**
 * Controller responsible for dealing with the requests from the react app.
 */
abstract class AbstractProxyController extends AbstractAction implements
    CsrfAwareActionInterface,
    HttpGetActionInterface,
    HttpPostActionInterface
{
    /**
     * Config paths
     */
    public const BACKEND_PATH = 'live_search/backend_path';

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var ServiceClientInterface
     */
    private ServiceClientInterface $serviceClient;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

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
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Execute middleware call
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();
        $headers = [
            'Magento-Website-Code' => $this->getRequest()->getHeader('Magento-Website-Code', ''),
            'Magento-Store-Code' => $this->getRequest()->getHeader('Magento-Store-Code', ''),
            'Magento-Store-View-Code' => $this->getRequest()->getHeader('Magento-Store-View-Code', ''),
            'Magento-Is-Preview' => $this->getRequest()->getHeader('Magento-Is-Preview', '')
        ];
        $payload = $this->getRequest()->getContent();
        $path = $this->config->getValue(static::BACKEND_PATH);

        $result = [];
        try {
            $result = $this->serviceClient->request($headers, $path, $payload);
        } catch (KeyInvalidException $e) {
            $jsonResult->setHttpResponseCode(403);
        } catch (ApiException $e) {
            $jsonResult->setHttpResponseCode(500);
        }

        return $jsonResult->setData($result);
    }

    /**
     * Check is user allowed to access Live Search
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Magento_LiveSearch::livesearch');
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function _processUrlKeys(): bool
    {
        $isValid = false;
        if ($this->_auth->isLoggedIn()) {
            if ($this->_backendUrl->useSecretKey()) {
                $isValid = $this->_validateSecretKey();
            } else {
                $isValid = true;
            }
        }
        if (!$isValid) {
            $error = json_encode(
                [
                'errors' => [
                    [
                        'message' => 'Authentication failed'
                    ]
                ]
                ]
            );
            $this->getResponse()->representJson($error);
        }
        return true;
    }
}
