<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Sidebar;

use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\UrlResolver;
use Magento\Framework\View\Element\Template\Context;

class Search extends AbstractClass
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Settings $settingsHelper,
        Date $dateHelper,
        Data $dataHelper,
        UrlResolver $urlResolver,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $settingsHelper, $dateHelper, $dataHelper, $configProvider, $data);
        $this->urlResolver = $urlResolver;
        $this->configProvider = $configProvider;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("Amasty_Blog::sidebar/search.phtml");
        $this->addAmpTemplate("Amasty_Blog::amp/sidebar/search.phtml");
        $this->setRoute('display_search');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getBlockHeader()
    {
        return __('Search the blog');
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->urlResolver->getSearchPageUrl();
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->stripTags($this->getRequest()->getParam('query', ''));
    }

    /**
     * @return string
     */
    public function getAmpSearchUrl()
    {
        return str_replace(['https:', 'http:'], '', $this->getSearchUrl());
    }

    public function getMinCharactersLength(): int
    {
        return $this->configProvider->getMinCharacterLength();
    }

    public function getLiveSearchUrl(): string
    {
        return $this->_urlBuilder->getUrl('amblog/ajax/liveSearch');
    }
}
