<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Posts;

/**
 * Class NewAction
 */
class NewAction extends \Amasty\Blog\Controller\Adminhtml\Posts
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
