<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Author;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Helper\Image;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SmallImage implements ArgumentInterface
{
    /**
     * @var Image
     */
    private $imageHelper;

    public function __construct(Image $imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param AuthorInterface $author
     * @return string|null
     */
    public function getImage(AuthorInterface $author): ?string
    {
        try {
            return $this->imageHelper->getResizedImageUrl($author->getImage());
        } catch (\Exception $e) {
            return null;
        }
    }
}
