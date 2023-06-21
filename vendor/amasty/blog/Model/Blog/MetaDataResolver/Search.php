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

class Search
{
    /**
     * @var MetaDataResolver
     */
    private $resolver;

    public function __construct(MetaDataResolver $metaDataResolver)
    {
        $this->resolver = $metaDataResolver;
    }

    public function resolve(ResultPage $resultPage, string $searchText): void
    {
        $this->resolver->preparePageMetadata(
            $resultPage,
            __("Search results for '%1'", $searchText)->render(),
            $searchText,
            __("There are following posts founded for the search request '%1'", $searchText)->render(),
            '',
            __("Search results for '%1'", $searchText)->render()
        );
    }
}
