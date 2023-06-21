<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Posts\Meta;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Blog\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class OgMarkup implements ArgumentInterface
{
    /**
     * @var \Amasty\Blog\Model\Blog\Registry
     */
    private $registry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    public function getPost()
    {
        return $this->registry->registry(Registry::CURRENT_POST);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreName()
    {
        return $this->storeManager->getStore()->getName();
    }
}
