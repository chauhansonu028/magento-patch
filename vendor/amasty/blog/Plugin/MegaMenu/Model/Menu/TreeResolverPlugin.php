<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Plugin\MegaMenu\Model\Menu;

use Amasty\Blog\Block\Link;
use Amasty\Blog\Model\UrlResolver;

class TreeResolverPlugin
{
    /**
     * @var \Amasty\Blog\Helper\Settings
     */
    private $settings;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var Link
     */
    private $link;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        \Amasty\Blog\Helper\Settings $settings,
        \Magento\Framework\UrlInterface $url,
        Link $link,
        UrlResolver $urlResolver
    ) {
        $this->settings = $settings;
        $this->url = $url;
        $this->link = $link;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param \Amasty\MegaMenuLite\Model\Menu\TreeResolver $subject
     * @param $items
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetAdditionalLinks(
        \Amasty\MegaMenuLite\Model\Menu\TreeResolver $subject,
        $items
    ) {
        if (!$this->settings->showInNavMenu()) {
            return $items;
        }

        $url = $this->urlResolver->getBlogUrl();
        $items[] = [
            'name' => $this->link->getLabel(),
            'id' => 'amasty_blog',
            'url' => $url,
            'has_active' => false,
            'content' => '',
            'width' => 1,
            'is_active' => $url == $this->url->getCurrentUrl()
        ];

        return $items;
    }
}
