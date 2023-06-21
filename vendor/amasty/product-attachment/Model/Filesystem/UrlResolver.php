<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Filesystem;

use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Filesystem\File;
use Magento\Framework\UrlInterface;

class UrlResolver
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        UrlInterface $urlBuilder,
        File $file
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->file = $file;
    }

    public function getIconUrlByName($name)
    {
        if (!($icon = $this->file->getFilePath($name, Directory::ICON))) {
            return false;
        }

        return $this->getUrlByDirectory($icon);
    }

    public function getCategoryIconUrlByName($name)
    {
        if (!($icon = $this->file->getFilePath($name, Directory::CATEGORY_ICON))) {
            return false;
        }

        return $this->getUrlByDirectory($icon);
    }

    private function getUrlByDirectory($icon): string
    {
        $baseUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        $baseUrl = trim(str_replace('index.php', '', $baseUrl), '/');

        return $baseUrl . '/' . $icon;
    }

    public function getAttachmentUrlByName($name)
    {
        if (!($file = $this->file->getFilePath($name, Directory::ATTACHMENT))) {
            return false;
        }

        $baseUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        $baseUrl = trim(str_replace('index.php', '', $baseUrl), '/');

        return $baseUrl . '/' . $file;
    }
}
