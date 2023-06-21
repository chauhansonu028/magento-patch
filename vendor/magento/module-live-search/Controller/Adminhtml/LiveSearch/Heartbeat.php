<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Controller\Adminhtml\LiveSearch;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Controller to keep magento session alive in react app
 */
class Heartbeat extends AbstractAction implements
    CsrfAwareActionInterface,
    HttpGetActionInterface,
    HttpPostActionInterface
{
    /**
     * @var string
     */
    private const SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var int|null
     */
    private ?int $sessionLifetime;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $config
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $config,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     *
     * @return Json
     */
    public function execute(): Json
    {
        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setHeader('session_lifetime', $this->getSessionLifetime());
        return $jsonResult;
    }

    /**
     * Get session lifetime
     *
     * @return int
     */
    private function getSessionLifetime(): int
    {
        if (!isset($this->sessionLifetime)) {
            $this->sessionLifetime = (int) $this->config->getValue(
                self::SESSION_LIFETIME,
                ScopeInterface::SCOPE_STORES
            );
        }
        return $this->sessionLifetime;
    }

    /**
     * @inheritdoc
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
            $this->_redirect($this->_backendUrl->getStartupPageUrl());
        }
        return $isValid;
    }
}
