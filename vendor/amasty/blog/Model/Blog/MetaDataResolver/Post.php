<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Blog\MetaDataResolver;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Blog\MetaDataResolver;
use Magento\Framework\View\Result\Page as ResultPage;

class Post
{
    /**
     * @var MetaDataResolver
     */
    private $resolver;

    public function __construct(MetaDataResolver $metaDataResolver)
    {
        $this->resolver = $metaDataResolver;
    }

    public function resolve(ResultPage $resultPage, PostInterface $post): void
    {
        $this->resolver->preparePageMetadata(
            $resultPage,
            (string)$post->getMetaTitle(),
            (string)$post->getMetaTags(),
            (string)($post->getMetaDescription() ?: $this->resolver->cutDescription((string)$post->getShortContent())),
            (string)$post->getUrl(),
            (string)$post->getTitle(),
            $this->resolver->getMetaRobots($post)
        );
    }
}
