<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api\Data;

interface TagInterface
{
    public const ROUTE_TAG = 'tag';

    public const TAG_ID = 'tag_id';

    public const NAME = 'name';

    public const URL_KEY = 'url_key';

    public const META_TITLE = 'meta_title';

    public const META_TAGS = 'meta_tags';

    public const META_DESCRIPTION = 'meta_description';

    public const META_ROBOTS = 'meta_robots';

    public const STORE_ID = 'store_id';

    public const FIELDS_BY_STORE = [
        'general' => [
            self::NAME,
            self::URL_KEY,
        ],
        'meta_data' => [
            self::META_TITLE,
            self::META_TAGS,
            self::META_DESCRIPTION,
            self::META_ROBOTS,
        ]
    ];

    /**
     * @return int
     */
    public function getTagId();

    /**
     * @param int $tagId
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setTagId($tagId);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getUrlKey();

    /**
     * @param string|null $urlKey
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string|null $metaTitle
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string|null
     */
    public function getMetaTags();

    /**
     * @param string|null $metaTags
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setMetaTags($metaTags);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Blog\Api\Data\TagInterface
     */
    public function setStoreId($storeId);

    public function getMetaRobots(): ?string;

    public function setMetaRobots(string $metaRobots): void;
}
