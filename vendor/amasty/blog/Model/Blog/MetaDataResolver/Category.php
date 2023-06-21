<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Blog\MetaDataResolver;

use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Model\Blog\MetaDataResolver;
use Magento\Framework\View\Result\Page as ResultPage;

class Category
{
    /**
     * @var MetaDataResolver
     */
    private $resolver;

    public function __construct(MetaDataResolver $metaDataResolver)
    {
        $this->resolver = $metaDataResolver;
    }

    public function resolve(ResultPage $resultPage, CategoryInterface $category): void
    {
        $this->resolver->preparePageMetadata(
            $resultPage,
            (string)$category->getMetaTitle(),
            (string)$category->getMetaTags(),
            (string)$category->getMetaDescription(),
            (string)$category->getUrl(),
            (string)$category->getName(),
            $this->resolver->getMetaRobots($category)
        );
    }
}
