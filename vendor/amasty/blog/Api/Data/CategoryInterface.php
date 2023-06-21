<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api\Data;

interface CategoryInterface
{
    public const ROUTE_CATEGORY = 'category';

    public const CATEGORY_ID = 'category_id';

    public const NAME = 'name';

    public const URL_KEY = 'url_key';

    public const STATUS = 'status';

    public const SORT_ORDER = 'sort_order';

    public const META_TITLE = 'meta_title';

    public const META_TAGS = 'meta_tags';

    public const META_DESCRIPTION = 'meta_description';

    public const META_ROBOTS = 'meta_robots';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    public const PARENT_ID = 'parent_id';

    public const PATH = 'path';

    public const LEVEL = 'level';

    public const ROOT_CATEGORY_ID = "0";

    public const STORE_ID = "store_id";

    public const DESCRIPTION = "description";

    public const FIELDS_BY_STORE = [
        'general' => [
            self::NAME,
            self::STATUS,
            self::URL_KEY,
            self::DESCRIPTION,
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
    public function getCategoryId();

    /**
     * @param int $categoryId
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setCategoryId($categoryId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getUrlKey();

    /**
     * @param string $urlKey
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string|null $metaTitle
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string|null
     */
    public function getMetaTags();

    /**
     * @param string|null $metaTags
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setMetaTags($metaTags);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param int $parentId
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setParentId($parentId);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $path
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setPath($path);

    /**
     * @return int
     */
    public function getLevel();

    /**
     * @param int $level
     *
     * @return \Amasty\Blog\Api\Data\CategoryInterface
     */
    public function setLevel($level);

    /**
     * @return bool
     */
    public function hasChildren();

    /**
     * @return \Amasty\Blog\Model\ResourceModel\Categories\Collection
     */
    public function getChildrenCollection();

    /**
     * @return bool
     */
    public function hasActiveChildren();

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string|null $description
     */
    public function setDescription(string $description): void;

    public function getMetaRobots(): ?string;

    public function setMetaRobots(string $metaRobots): void;
}
