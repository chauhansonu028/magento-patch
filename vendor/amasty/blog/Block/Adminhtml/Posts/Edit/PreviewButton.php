<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Adminhtml\Posts\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class PreviewButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Preview'),
            'class' => 'amblog-preview-button',
            'on_click' => '',
            'sort_order' => 0,
        ];
    }
}
