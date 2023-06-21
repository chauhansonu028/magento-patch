<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Category;

use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Model\Blog\Registry as BlogRegistry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Description implements ArgumentInterface
{
    /**
     * @var BlogRegistry
     */
    private $blogRegistry;

    public function __construct(
        BlogRegistry $blogRegistry
    ) {
        $this->blogRegistry = $blogRegistry;
    }

    public function getCategoryDescription(): string
    {
        /** @var CategoryInterface $currentCategory **/
        $currentCategory = $this->blogRegistry->registry(BlogRegistry::CURRENT_CATEGORY);

        return $currentCategory ? $currentCategory->getDescription() : '';
    }
}
