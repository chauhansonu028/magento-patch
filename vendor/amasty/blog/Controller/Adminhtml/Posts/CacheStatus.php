<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Posts;

use Amasty\Blog\Model\Cache\Type\Blog;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Url;

class CacheStatus extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var StateInterface
     */
    private $cacheState;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Url $urlHelper,
        StateInterface $cacheState
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlHelper = $urlHelper;
        $this->cacheState = $cacheState;
    }

    public function execute(): ResultInterface
    {
        if ($this->getRequest()->isAjax()) {
            return $this->resultJsonFactory->create()->setData(
                [
                    'blogCacheStatus' => $this->cacheState->isEnabled(Blog::TYPE_IDENTIFIER)
                ]
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = $this->urlHelper->getUrl('amblog/post/preview');

        return $resultRedirect->setUrl($url);
    }
}
