<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Plugin\Widget\Model\Widget\Instance;

use Amasty\Blog\Observer\ApplyBlogLayout;
use Amasty\Blog\Plugin\Framework\View\Element\Html\Select\AddBlogSectionToWidgetRenderPlaces;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

class AddBlogLayoutHandles
{
    public function beforeBeforeSave(WidgetInstance $widgetInstance): void
    {
        $variableAccessor = function (): void {
            if (!empty($this->_layoutHandles)) {
                $this->_layoutHandles = array_merge(
                    $this->_layoutHandles,
                    [
                        AddBlogSectionToWidgetRenderPlaces::BLOG_LISTINGS => ApplyBlogLayout::LISTING_UPDATE,
                        AddBlogSectionToWidgetRenderPlaces::BLOG_POST => ApplyBlogLayout::POST_UPDATE
                    ]
                );
            }
        };
        $variableAccessor = \Closure::bind($variableAccessor, $widgetInstance, $widgetInstance);
        $variableAccessor();
    }
}
