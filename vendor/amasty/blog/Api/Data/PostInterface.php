<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api\Data;

interface PostInterface
{
    public const POST_ID = 'post_id';

    public const TAGS = 'tags';

    public const TAG_IDS = 'tag_ids';

    public const STORES = 'stores';

    public const CATEGORIES = 'categories';

    public const STATUS = 'status';

    public const TITLE = 'title';

    public const URL_KEY = 'url_key';

    public const SHORT_CONTENT = 'short_content';

    public const FULL_CONTENT = 'full_content';

    public const POSTED_BY = 'posted_by';

    public const META_TITLE = 'meta_title';

    public const META_TAGS = 'meta_tags';

    public const META_DESCRIPTION = 'meta_description';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    public const PUBLISHED_AT = 'published_at';

    public const RECENTLY_COMMENTED_AT = 'recently_commented_at';

    public const USER_DEFINE_PUBLISH = 'user_define_publish';

    public const NOTIFY_ON_ENABLE = 'notify_on_enable';

    public const DISPLAY_SHORT_CONTENT = 'display_short_content';

    public const COMMENTS_ENABLED = 'comments_enabled';

    public const VIEWS = 'views';

    public const POST_THUMBNAIL = 'post_thumbnail';

    public const LIST_THUMBNAIL = 'list_thumbnail';

    public const THUMBNAIL_URL = 'thumbnail_url';

    public const GRID_CLASS = 'grid_class';

    public const CANONICAL_URL = 'canonical_url';

    public const POST_THUMBNAIL_ALT = 'post_thumbnail_alt';

    public const LIST_THUMBNAIL_ALT = 'list_thumbnail_alt';

    public const AUTHOR = 'author';

    public const AUTHOR_ID = 'author_id';

    public const RELATED_POST_IDS = 'related_post_ids';

    public const IS_FEATURED = 'is_featured';

    public const IS_OG_META_ENABLED = 'is_open_graph_enabled';

    public const OG_META_TITLE = 'open_graph_meta_title';

    public const OG_META_DESCRIPTION = 'open_graph_meta_description';

    public const OG_META_TYPE = 'open_graph_meta_type';

    public const STORE_ID = 'store_id';

    public const META_ROBOTS = 'meta_robots';

    public const POSTS_STORE_TABLE = 'amasty_blog_posts_store';

    public const ALL_STORE_VIEWS = 0;

    public const EDITED_AT = 'edited_at';

    public const FIELDS_BY_STORE = [
        'wrapper-content' => [
            'children' => [
                'content' => [
                    self::TITLE,
                    self::URL_KEY,
                    self::SHORT_CONTENT,
                    self::FULL_CONTENT,
                    'alt_group' => [
                        self::POST_THUMBNAIL_ALT,
                        self::LIST_THUMBNAIL_ALT,
                    ]
                ]
            ]
        ],
        'wrapper' => [
            'children' => [
                'meta_data' => [
                    self::META_TITLE,
                    self::META_TAGS,
                    self::META_DESCRIPTION,
                    self::META_ROBOTS,
                    self::CANONICAL_URL,
                ],
                'publish_status' => [
                    self::STATUS,
                    self::PUBLISHED_AT,
                ]
            ]
        ],
    ];

    /**
     * @return int
     */
    public function getPostId();

    /**
     * @return string
     */
    public function getTags();

    /**
     * @return array
     */
    public function getStores();

    /**
     * @return array
     */
    public function getCategories();

    /**
     * @param int $postId
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setPostId($postId);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getUrlKey();

    /**
     * @param string $urlKey
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return string|null
     */
    public function getShortContent();

    /**
     * @param string|null $shortContent
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setShortContent($shortContent);

    /**
     * @return string
     */
    public function getFullContent();

    /**
     * @param string $fullContent
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setFullContent($fullContent);

    /**
     * @return string
     */
    public function getPostedBy();

    /**
     * @param string $postedBy
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setPostedBy($postedBy);

    /**
     * @return string
     */
    public function getRelatedPostIds();

    /**
     * @param string $ids
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setRelatedPostIds($ids);

    /**
     * @return string|null
     */
    public function getFacebookProfile();

    /**
     * @param string|null $facebookProfile
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setFacebookProfile($facebookProfile);

    /**
     * @return string|null
     */
    public function getTwitterProfile();

    /**
     * @param string|null $twitterProfile
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setTwitterProfile($twitterProfile);

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string|null $metaTitle
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string|null
     */
    public function getMetaTags();

    /**
     * @param string|null $metaTags
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setMetaTags($metaTags);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getPublishedAt();

    /**
     * @param string $publishedAt
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setPublishedAt($publishedAt);

    /**
     * @return string
     */
    public function getRecentlyCommentedAt();

    /**
     * @param string $recentlyCommentedAt
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setRecentlyCommentedAt($recentlyCommentedAt);

    /**
     * @return int
     */
    public function getUserDefinePublish();

    /**
     * @param int $userDefinePublish
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setUserDefinePublish($userDefinePublish);

    /**
     * @return int
     */
    public function getNotifyOnEnable();

    /**
     * @param int $notifyOnEnable
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setNotifyOnEnable($notifyOnEnable);

    /**
     * @return int
     */
    public function getDisplayShortContent();

    /**
     * @param int $displayShortContent
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setDisplayShortContent($displayShortContent);

    /**
     * @return int
     */
    public function getCommentsEnabled();

    /**
     * @param int $commentsEnabled
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setCommentsEnabled($commentsEnabled);

    /**
     * @return int
     */
    public function getViews();

    /**
     * @param int $views
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setViews($views);

    /**
     * @return string|null
     */
    public function getPostThumbnail();

    /**
     * @param string|null $postThumbnail
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setPostThumbnail($postThumbnail);

    /**
     * @param $name
     * @param $thumbnail
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setThumbnail($name, $thumbnail);

    /**
     * @return string|null
     */
    public function getListThumbnail();

    /**
     * @param string|null $listThumbnail
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setListThumbnail($listThumbnail);

    /**
     * @return string|null
     */
    public function getThumbnailUrl();

    /**
     * @param string|null $thumbnailUrl
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setThumbnailUrl($thumbnailUrl);

    /**
     * @return string
     */
    public function getGridClass();

    /**
     * @param string $gridClass
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setGridClass($gridClass);

    /**
     * @return string|null
     */
    public function getCanonicalUrl();

    /**
     * @param string|null $canonicalUrl
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setCanonicalUrl($canonicalUrl);

    /**
     * @return string
     */
    public function getPostThumbnailAlt();

    /**
     * @param string $postThumbnailAlt
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setPostThumbnailAlt($postThumbnailAlt);

    /**
     * @return string
     */
    public function getListThumbnailAlt();

    /**
     * @param string $listThumbnailAlt
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setListThumbnailAlt($listThumbnailAlt);

    /**
     * @return int
     */
    public function getAuthorId();

    /**
     * @param int $authorId
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setAuthorId($authorId);

    /**
     * @return AuthorInterface
     */
    public function getAuthor();

    /**
     * @param AuthorInterface $author
     *
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setAuthor(\Amasty\Blog\Api\Data\AuthorInterface $author);

    /**
     * @return array
     */
    public function getTagIds();

    /**
     * @return bool
     */
    public function isFeatured();

    /**
     * @return bool
     */
    public function isOpenGraphEnabled();

    /**
     * @return string|null
     */
    public function getOpenGraphTitle();

    /**
     * @param string|null $openGraphTitle
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setOpenGraphTitle($openGraphTitle);

    /**
     * @return string|null
     */
    public function getOpenGraphDescription();

    /**
     * @param string|null $openGraphDescription
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setOpenGraphDescription($openGraphDescription);

    /**
     * @return string|null
     */
    public function getOpenGraphType();

    /**
     * @param string|null $openGraphType
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    public function setOpenGraphType($openGraphType);

    /**
     * @return string|null
     */
    public function getEditedAt(): ?string;

    /**
     * @param string $editedAt
     * @return PostInterface
     */
    public function setEditedAt(string $editedAt): PostInterface;

    public function getMetaRobots(): ?string;

    public function setMetaRobots(string $metaRobots): void;
}
