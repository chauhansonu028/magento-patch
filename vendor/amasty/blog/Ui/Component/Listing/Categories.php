<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Ui\Component\Listing;

class Categories extends \Amasty\Blog\Ui\Component\Form\Categories
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $collection = $this->getCategoriesCollectionFactory()->create()->addDefaultStore();
        foreach ($collection as $category) {
            $options[] = [
                'value' => $category->getCategoryId(),
                'label' => $category->getName()
            ];
        }

        return $options;
    }
}
