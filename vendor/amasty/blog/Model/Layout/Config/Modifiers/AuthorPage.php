<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout\Config\Modifiers;

use Amasty\Blog\Model\Layout\Config\DynamicModifierInterface;
use Amasty\Blog\Model\Layout\ConfigFactory;
use Amasty\Blog\Model\Source\Layout;

class AuthorPage implements DynamicModifierInterface
{
    const AUTHOR_ABOUT_IDENTIFIER = 'author_about';

    public function modify(array $layoutConfig): array
    {
        $pageTypeIdentifier = $layoutConfig[ConfigFactory::LAYOUT] ?? Layout::ONE_COLUMN_LAYOUT;

        if (in_array($pageTypeIdentifier, [Layout::THREE_COLUMNS_LAYOUT, Layout::TWO_COLUMNS_RIGHT_LAYOUT])) {
            $layoutConfig[ConfigFactory::RIGHT_SIDE] = $layoutConfig[ConfigFactory::RIGHT_SIDE] ?? [];
            array_unshift($layoutConfig[ConfigFactory::RIGHT_SIDE], self::AUTHOR_ABOUT_IDENTIFIER);
        } elseif ($pageTypeIdentifier === Layout::TWO_COLUMNS_LEFT_LAYOUT) {
            $layoutConfig[ConfigFactory::LEFT_SIDE] = $layoutConfig[ConfigFactory::LEFT_SIDE] ?? [];
            array_unshift($layoutConfig[ConfigFactory::LEFT_SIDE], self::AUTHOR_ABOUT_IDENTIFIER);
        }

        return $layoutConfig;
    }
}
