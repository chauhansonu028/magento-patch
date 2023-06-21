<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Image;

use Amasty\Blog\Model\ImageProcessor;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImagePathConverter
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function getImagePath(string $image): string
    {
        if (strpos($image, '/') === false) {
            $mediaPath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $imagePath = $mediaPath . ImageProcessor::BLOG_MEDIA_PATH . '/' . $image;
        } else {
            $imagePath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . trim($image, '/');
        }

        return $imagePath;
    }
}
