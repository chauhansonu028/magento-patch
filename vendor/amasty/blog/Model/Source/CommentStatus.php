<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class
 */
class CommentStatus implements ArrayInterface
{
    const STATUS_PENDING = 1;

    const STATUS_APPROVED = 2;

    const STATUS_REJECTED = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => self::STATUS_APPROVED, 'label' => __('Approved')],
            ['value' => self::STATUS_REJECTED, 'label' => __('Rejected')]
        ];
    }
}
