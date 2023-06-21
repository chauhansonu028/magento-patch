<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_BlogPageBuilder
*/


declare(strict_types=1);

namespace Amasty\BlogPageBuilder\Model\Renderer\WidgetDirective;

class Wrapper extends \Amasty\Blog\Model\Di\Wrapper
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        $name = ''
    ) {
        parent::__construct($objectManagerInterface, 'Magento\PageBuilder\Model\Stage\Renderer\WidgetDirective');
    }
}
