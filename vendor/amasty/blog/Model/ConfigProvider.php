<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Catalog\Model\Product\Image;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    public const PREVIOUS_NEXT_NAVIGATION = 'post/previous_next_navigation';
    public const SEARCH_ENGINE_ROUTE = 'search_engine/route';
    public const REDIRECT_URL_POSTFIX = 'redirect/url_postfix';
    public const IS_ASK_EMAIL = 'comments/ask_email';
    public const IS_ASK_NAME = 'comments/ask_name';
    public const IS_NOTIFY_ABOUT_REPLIES = 'comments/notify_about_replies';
    public const NOTIFY_ABOUT_REPLIES_SENDER = 'comments/sender';
    public const NOTIFY_ABOUT_REPLIES_TEMPLATE = 'comments/email_template';
    public const IS_SHOW_GDPR = 'comments/gdpr';
    public const GDPR_TEXT = 'comments/gdpr_text';
    public const TITLE_PREFIX = 'search_engine/title_prefix';
    public const TITLE_SUFFIX = 'search_engine/title_suffix';
    public const TITLE = 'search_engine/title';
    public const META_TITLE = 'search_engine/meta_title';
    public const META_DESCRIPTION = 'search_engine/meta_description';
    public const META_KEYWORDS = 'search_engine/meta_keywords';
    public const META_ROBOTS = 'search_engine/meta_robots';
    public const DATE_MANNER = 'post/date_manner';
    public const IS_DISPLAY_READ_TIME = 'post/display_read_time';
    public const XML_PATH_POST_PRODUCT_SHOW_ON_PDP = 'posts_products_relation/show_related_posts_on_pp';
    public const XML_PATH_POST_PRODUCT_PDP_BLOCK_TITLE = 'posts_products_relation/related_posts_tab_title';
    public const XML_PATH_POST_PRODUCT_SHOW_ON_POST_PAGE = 'posts_products_relation/show_rp_on_post_page';
    public const XML_PATH_POST_PRODUCT_POST_PAGE_BLOCK_TITLE = 'posts_products_relation/rp_block_title';
    public const XML_PATH_ACCELERATED_MOBILE_PAGES = 'accelerated_mobile_pages/enabled';
    public const XML_PATH_LAYOUT_CONFIG_PREFIX = 'layout/';
    public const XML_PATH_SEARCH_CONFIG_PREFIX = 'search/';
    public const IS_SHOW_SUMMARY_BLOCK = 'list/display_blog_summary';
    public const SUMMARY_CMS_BLOCK = 'list/summary_cms_block';
    public const IS_SHOW_EDITED_AT = 'post/display_edited_date';
    public const EDITED_AT_DATE_MANNER = 'post/edited_date_format';

    public const CATEGORY_LIMIT_ON_POST = 'category/limit_on_post';
    public const MIN_CHARACTER_LENGTH = 'min_character_length';
    public const ITEMS_PER_ENTITY = 'items_per_entity';

    public const DISPLAY_AUTHOR = 'post/display_author';
    public const ICON_COLOR_CLASS = 'style/color_sheme';

    public const FONTS = 'fonts/';
    public const FONT_TYPE = 'font_type';
    public const GOOGLE_FONT = 'google_font';
    public const GOOGLE_FONT_STYLE = 'google_font_style';

    /**
     * @var string
     */
    protected $pathPrefix = 'amblog/';

    /**
     * @var FilterManager
     */
    private $filterManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FilterManager $filterManager
    ) {
        parent::__construct($scopeConfig);
        $this->filterManager = $filterManager;
    }

    public function isShowSummaryBlock(): bool
    {
        return $this->isSetFlag(self::IS_SHOW_SUMMARY_BLOCK);
    }

    public function getSummaryBlockId(): int
    {
        return (int)$this->getValue(self::SUMMARY_CMS_BLOCK);
    }

    /**
     * @param string|null $scopeCode
     * @return bool
     */
    public function isAskEmail($scopeCode = null)
    {
        return (bool)$this->getValue(self::IS_ASK_EMAIL, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     * @return bool
     */
    public function isAskName($scopeCode = null)
    {
        return (bool)$this->getValue(self::IS_ASK_NAME, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     * @return bool
     */
    public function isShowGdpr($scopeCode = null)
    {
        return (bool)$this->getValue(self::IS_SHOW_GDPR, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     * @return string
     */
    public function getGdprText($scopeCode = null)
    {
        return $this->filterManager->stripTags(
            $this->getValue(self::GDPR_TEXT, $scopeCode),
            [
                'allowableTags' => '<a>',
                'escape' => false
            ]
        );
    }

    public function isAmpEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::XML_PATH_ACCELERATED_MOBILE_PAGES, $storeId);
    }

    public function getImageQuality(): int
    {
        return (int)$this->scopeConfig->getValue(Image::XML_PATH_JPEG_QUALITY, ScopeInterface::SCOPE_STORE);
    }

    public function getBlogPostfix(?StoreInterface $store = null): string
    {
        return (string)$this->getValue(self::REDIRECT_URL_POSTFIX, $store);
    }

    public function getSeoRoute(?StoreInterface $store = null): string
    {
        return trim((string)$this->getValue(self::SEARCH_ENGINE_ROUTE, $store));
    }

    public function getTitleSuffix(): string
    {
        return (string)$this->getValue(self::TITLE_SUFFIX);
    }

    public function getTitlePrefix(): string
    {
        return (string)$this->getValue(self::TITLE_PREFIX);
    }

    public function getMetaTitle(): string
    {
        return (string)$this->getValue(self::META_TITLE);
    }

    public function getMetaTags(): string
    {
        return (string)$this->getValue(self::META_KEYWORDS);
    }

    public function getMetaDescription(): string
    {
        return (string)$this->getValue(self::META_DESCRIPTION);
    }

    public function getTitle(): string
    {
        return (string)$this->getValue(self::TITLE);
    }

    public function isNotifyAboutReplies(): bool
    {
        return (bool)$this->getValue(self::IS_NOTIFY_ABOUT_REPLIES);
    }

    public function notifyAboutRepliesSender(): ?string
    {
        return $this->getValue(self::NOTIFY_ABOUT_REPLIES_SENDER);
    }

    public function notifyAboutRepliesTemplate(): ?string
    {
        return $this->getValue(self::NOTIFY_ABOUT_REPLIES_TEMPLATE);
    }

    public function isShowPostPageBlockOnProductPage(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_POST_PRODUCT_SHOW_ON_PDP);
    }

    public function getPostPageBlockTitleOnProductPage(): string
    {
        return (string)$this->getValue(self::XML_PATH_POST_PRODUCT_PDP_BLOCK_TITLE);
    }

    public function isShowPostPageBlockOnPostPage(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_POST_PRODUCT_SHOW_ON_POST_PAGE);
    }

    public function getPostPageBlockTitleOnPostPage(): string
    {
        return (string)$this->getValue(self::XML_PATH_POST_PRODUCT_POST_PAGE_BLOCK_TITLE);
    }

    public function getCategoryLimitOnPost(): int
    {
        return (int)$this->getValue(self::CATEGORY_LIMIT_ON_POST);
    }

    public function getIconColorClass(): string
    {
        return (string)$this->getValue(self::ICON_COLOR_CLASS);
    }

    public function isShowAuthorInfo(): bool
    {
        return $this->isSetFlag(self::DISPLAY_AUTHOR);
    }

    public function getLayoutConfigByIdentifier(string $identifier, ?int $storeId = null): string
    {
        return (string)$this->getValue(self::XML_PATH_LAYOUT_CONFIG_PREFIX . $identifier, $storeId);
    }

    public function isPreviousNextNavigation(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::PREVIOUS_NEXT_NAVIGATION, $storeId);
    }

    public function getDateFormat(): string
    {
        return (string)$this->getValue(self::DATE_MANNER);
    }

    public function getEditedAtDateFormat(): string
    {
        return (string)$this->getValue(self::EDITED_AT_DATE_MANNER);
    }

    public function isShowEditedAt(): bool
    {
        return $this->isSetFlag(self::IS_SHOW_EDITED_AT);
    }

    public function getFontType(?int $storeId = null): string
    {
        return (string)$this->getValue(self::FONTS . self::FONT_TYPE, $storeId);
    }

    public function getGoogleFontSetting(): string
    {
        return (string)$this->getValue(self::FONTS . self::GOOGLE_FONT);
    }

    public function getGoogleFontStyle(): string
    {
        return (string)$this->getValue(self::FONTS . self::GOOGLE_FONT_STYLE);
    }

    public function getMetaRobots(): string
    {
        return (string)$this->getValue(self::META_ROBOTS);
    }

    public function getMinCharacterLength(): int
    {
        return (int)$this->getValue(self::XML_PATH_SEARCH_CONFIG_PREFIX . self::MIN_CHARACTER_LENGTH);
    }

    public function getItemsPerEntity(): int
    {
        return (int)$this->getValue(self::XML_PATH_SEARCH_CONFIG_PREFIX . self::ITEMS_PER_ENTITY);
    }

    public function isDisplayReadTime(): bool
    {
        return $this->isSetFlag(self::IS_DISPLAY_READ_TIME);
    }
}
