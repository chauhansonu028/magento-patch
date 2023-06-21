<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\AbstractController;

use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Model\Blog\MetaDataResolver\Tag as MetaResolver;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\UrlResolver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Tag extends Action
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var MetaResolver
     */
    private $metaDataResolver;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        Registry $registry,
        UrlResolver $urlResolver,
        PageFactory $resultPageFactory,
        MetaResolver $metaDataResolver,
        TagRepositoryInterface $tagRepository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->urlResolver = $urlResolver;
        $this->resultPageFactory = $resultPageFactory;
        $this->metaDataResolver = $metaDataResolver;
        $this->tagRepository = $tagRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $page = $this->resultPageFactory->create();
                $tag = $this->tagRepository->getByIdAndStore($id, (int)$this->storeManager->getStore()->getId());
                $this->registry->register(Registry::CURRENT_TAG, $tag, true);

                $this->metaDataResolver->resolve($page, $tag);
                return $page;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->messageManager->addErrorMessage(
                    __('Something went wrong. Please review the error log.')
                );
            }
        }

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirect->setPath($this->urlResolver->getBlogUrl());
    }
}
