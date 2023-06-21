<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Icon;

use Amasty\ProductAttachment\Model\Filesystem\UrlResolver;
use Amasty\ProductAttachment\Model\Icon\FileType\TypeProcessorProvider;
use Magento\Framework\Filesystem\Io\File;

class GetIconForFile
{
    /**
     * @var ResourceModel\Icon
     */
    private $iconResource;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var File
     */
    private $file;

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    public function __construct(
        ResourceModel\Icon $iconResource,
        UrlResolver $urlResolver,
        File $file,
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->iconResource = $iconResource;
        $this->urlResolver = $urlResolver;
        $this->file = $file;
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    //TODO FileStatInterface
    public function byFileName($filename)
    {
        if (!empty($filename)) {
            $extension = $this->file->getPathInfo($filename)['extension'] ?? '';
            if (!empty($extension) && $iconImage = $this->iconResource->getExtensionIconImage($extension)) {
                return $this->urlResolver->getIconUrlByName($iconImage);
            }
        }

        return false;
    }

    public function byFileExtension(string $ext, int $type = 0): string
    {
        return $this->typeProcessorProvider->getProcessorByType($type)->getIcon($ext);
    }
}
