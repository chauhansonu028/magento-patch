<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\ContentPreparation\Preparers;

class ReplaceImagesToAmpNotation implements PreparerInterface
{
    public function prepare(string $content): string
    {
        return preg_replace(
            '/<img(.+?)\/?>/is',
            '<div class="amp-img-container"><amp-img $1 layout="fill"></amp-img></div>',
            $content
        );
    }
}
