<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Plugin\MegaMenu\Observer\Layout;

use Amasty\Blog\Helper\Data;

class HandlePlugin
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param \Amasty\MegaMenu\Observer\Layout\Handle $subject
     * @param \Closure $proceed
     * @param $observer
     */
    public function aroundExecute($subject, \Closure $proceed, $observer)
    {
        if (!$this->helper->isCurrentPageAmp()) {
            $proceed($observer);
        }
    }
}
