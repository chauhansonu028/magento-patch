<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Icon\FileType\Processor;

use Amasty\ProductAttachment\Model\Filesystem\UrlResolver;
use Amasty\ProductAttachment\Model\Icon\ResourceModel\Icon;

class FileLinkTypeProcessor implements TypeProcessorInterface
{
    /**
     * @var Icon
     */
    private $iconResource;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        Icon $iconResource,
        UrlResolver $urlResolver
    ) {
        $this->iconResource = $iconResource;
        $this->urlResolver = $urlResolver;
    }

    public function getIcon(?string $ext): string
    {
        if (!empty($ext) && $icon = $this->iconResource->getExtensionIconImage($ext)) {
            return $this->urlResolver->getIconUrlByName($icon);
        }

        return '';
    }
}
