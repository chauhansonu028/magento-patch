<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_BlogPageBuilder
*/


declare(strict_types=1);

namespace Amasty\BlogPageBuilder\Model\Config\ContentType\AdditionalData\Provider;

use Magento\PageBuilder\Model\Config\ContentType\AdditionalData\ProviderInterface;

class WidgetDataUrl implements ProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(\Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string $itemName
     * @return array
     */
    public function getData(string $itemName) : array
    {
        return [$itemName => $this->urlBuilder->getUrl('amasty_blog/contenttype_block/metadata')];
    }
}
