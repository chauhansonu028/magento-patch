<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Layout;

class Account implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Session $customerSession,
        ResultFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        if ($this->customerSession->authenticate()) {
            /** @var ResultInterface $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(__('Blog Posts'));
        } else {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultPage = $resultPage->setPath(Url::ROUTE_ACCOUNT_LOGIN);
        }

        return $resultPage;
    }
}
