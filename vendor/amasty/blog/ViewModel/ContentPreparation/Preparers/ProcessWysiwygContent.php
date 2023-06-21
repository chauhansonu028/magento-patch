<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\ContentPreparation\Preparers;

use Magento\Catalog\Helper\Data;

class ProcessWysiwygContent implements PreparerInterface
{
    /**
     * @var Data
     */
    private $magentoCatalogHelper;

    public function __construct(
        Data $magentoCatalogHelper
    ) {
        $this->magentoCatalogHelper = $magentoCatalogHelper;
    }

    public function prepare(string $content): string
    {
        return $this->magentoCatalogHelper->getPageTemplateProcessor()->filter($content);
    }
}
