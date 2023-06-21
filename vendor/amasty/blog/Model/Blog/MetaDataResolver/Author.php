<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Blog\MetaDataResolver;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Model\Blog\MetaDataResolver;
use Magento\Framework\View\Result\Page as ResultPage;

class Author
{
    /**
     * @var MetaDataResolver
     */
    private $resolver;

    public function __construct(MetaDataResolver $metaDataResolver)
    {
        $this->resolver = $metaDataResolver;
    }

    public function resolve(ResultPage $resultPage, AuthorInterface $author): void
    {
        $this->resolver->preparePageMetadata(
            $resultPage,
            (string)$author->getMetaTitle(),
            (string)$author->getMetaTags(),
            (string)($author->getMetaDescription()
                ?: $this->resolver->cutDescription((string)$author->getShortDescription())),
            (string)$author->getUrl(),
            __('Articles by %1', $author->getName())->render(),
            $this->resolver->getMetaRobots($author)
        );
    }
}
