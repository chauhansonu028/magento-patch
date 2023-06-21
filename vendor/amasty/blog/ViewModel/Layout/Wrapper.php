<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Layout;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Wrapper implements ArgumentInterface
{
    public function getBlockIdentifierByNameInLayout(string $nameInLayout): string
    {
        preg_match('@^([a-z._]+)\.([a-z_]+)(\.\d+)?$@', $nameInLayout, $matches);

        return $matches[2] ?? uniqid();
    }
}
