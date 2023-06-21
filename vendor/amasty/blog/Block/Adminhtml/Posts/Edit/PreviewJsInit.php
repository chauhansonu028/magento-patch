<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Adminhtml\Posts\Edit;

use Amasty\Blog\Model\Cache\Type\Blog;
use Magento\Framework\App\Cache\StateInterface;

class PreviewJsInit extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var StateInterface
     */
    private $cacheState;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Url $url,
        StateInterface $cacheState,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheState = $cacheState;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getPreviewActionUrl()
    {
        return $this->getUrl('amasty_blog/posts/preview');
    }

    public function getCacheStatusActionUrl(): string
    {
        return $this->getUrl('amasty_blog/posts/cacheStatus');
    }

    /**
     * @return string
     */
    public function getPreviewFrontUrl()
    {
        return $this->url->getUrl('amblog/post/preview');
    }

    public function isNewPost(): bool
    {
        return $this->getRequest()->getParam('id') === null;
    }

    public function isBlogCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(Blog::TYPE_IDENTIFIER);
    }
}
