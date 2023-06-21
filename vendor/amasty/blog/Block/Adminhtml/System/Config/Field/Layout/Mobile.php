<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Adminhtml\System\Config\Field\Layout;

class Mobile extends \Amasty\Blog\Block\Adminhtml\System\Config\Field\Layout
{
    protected function getLayouts(): array
    {
        return $this->layoutOptions->getMobileOptions();
    }
}
