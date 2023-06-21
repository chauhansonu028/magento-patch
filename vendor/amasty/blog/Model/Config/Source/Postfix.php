<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Postfix implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('No Postfix')],
            ['value' => '.html', 'label' => __('.html')],
            ['value' => '/', 'label' => '/']
        ];
    }
}
