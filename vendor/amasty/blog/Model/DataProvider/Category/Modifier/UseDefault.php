<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\DataProvider\Category\Modifier;

use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Model\DataProvider\AbstractModifier;

class UseDefault extends AbstractModifier
{
    /**
     * @return array
     */
    public function getFieldsByStore()
    {
        return CategoryInterface::FIELDS_BY_STORE;
    }
}
