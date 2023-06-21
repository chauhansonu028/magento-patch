<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Newsletter;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;

class Subscribe extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Blog::newsletter/subscribe.phtml';

    public function getFormActionUrl(): string
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
