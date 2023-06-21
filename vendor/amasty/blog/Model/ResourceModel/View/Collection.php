<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\View;

/**
 * Class
 */
class Collection extends \Amasty\Blog\Model\ResourceModel\Abstracts\Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Blog\Model\View::class, \Amasty\Blog\Model\ResourceModel\View::class);
    }
}
