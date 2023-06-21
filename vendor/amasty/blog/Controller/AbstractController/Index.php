<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\AbstractController;

use Amasty\Blog\Model\Blog\MetaDataResolver\Home as MetaResolver;
use Amasty\Blog\Model\Blog\Registry;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var MetaResolver
     */
    private $metaDataResolver;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MetaResolver $metaDataResolver,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->metaDataResolver = $metaDataResolver;
        $this->registry = $registry;
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $this->metaDataResolver->resolve($page);
        $this->registry->register(Registry::INDEX_PAGE, true, true);

        return $page;
    }
}
