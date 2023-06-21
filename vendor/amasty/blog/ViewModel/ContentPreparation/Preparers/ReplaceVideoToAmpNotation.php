<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\ContentPreparation\Preparers;

class ReplaceVideoToAmpNotation implements PreparerInterface
{
    public function prepare(string $content): string
    {
        return preg_replace(
            '/<video(.+?)\>(.+?)<\/video>/is',
            '<amp-video $1 layout="responsive">$2</amp-video>',
            $content
        );
    }
}
