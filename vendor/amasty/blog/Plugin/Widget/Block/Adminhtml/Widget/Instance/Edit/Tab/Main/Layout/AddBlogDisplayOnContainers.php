<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Plugin\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout;

use Amasty\Blog\Observer\ApplyBlogLayout;
use Amasty\Blog\Plugin\Framework\View\Element\Html\Select\AddBlogSectionToWidgetRenderPlaces;
use Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout as WidgetLayoutBlock;

class AddBlogDisplayOnContainers
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param WidgetLayoutBlock $subject
     * @param array $result
     * @return array
     * @see \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout::getDisplayOnContainers
     */
    public function afterGetDisplayOnContainers(
        WidgetLayoutBlock $subject,
        array $result
    ): array {
        return array_merge($result, $this->getBlogContainers());
    }

    public function getBlogContainers(): array
    {
        return [
            AddBlogSectionToWidgetRenderPlaces::BLOG_LISTINGS => [
                'label' => __('Posts Listing Page'),
                'code' => 'amblog_listing',
                'name' => AddBlogSectionToWidgetRenderPlaces::BLOG_LISTINGS,
                'layout_handle' => ApplyBlogLayout::LISTING_UPDATE,
                'is_anchor_only' => '',
                'product_type_id' => '',
            ],
            AddBlogSectionToWidgetRenderPlaces::BLOG_POST => [
                'label' => __('Post Page'),
                'code' => 'amblog_post',
                'name' => AddBlogSectionToWidgetRenderPlaces::BLOG_POST,
                'layout_handle' => ApplyBlogLayout::POST_UPDATE,
                'is_anchor_only' => '',
                'product_type_id' => '',
            ]
        ];
    }
}
