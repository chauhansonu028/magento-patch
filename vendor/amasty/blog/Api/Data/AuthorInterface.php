<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api\Data;

interface AuthorInterface
{
    public const ROUTE_AUTHOR = 'author';

    public const AUTHOR_ID = 'author_id';

    public const NAME = 'name';

    public const FACEBOOK_PROFILE = 'facebook_profile';

    public const TWITTER_PROFILE = 'twitter_profile';

    public const LINKEDIN_PROFILE = 'linkedin_profile';

    public const YOUTUBE_PROFILE = 'youtube_profile';

    public const INSTAGRAM_PROFILE = 'instagram_profile';

    public const TIKTOK_PROFILE = 'tiktok_profile';

    public const URL_KEY = 'url_key';

    public const META_TITLE = 'meta_title';

    public const META_TAGS = 'meta_tags';

    public const META_ROBOTS = 'meta_robots';

    public const META_DESCRIPTION = 'meta_description';

    public const STORE_ID = 'store_id';

    public const JOB_TITLE = 'job_title';

    public const SHORT_DESCRIPTION = 'short_description';

    public const DESCRIPTION = 'description';

    public const IMAGE = 'image';

    public const FIELDS_BY_STORE = [
        'general' => [
            self::NAME,
            self::JOB_TITLE,
            self::DESCRIPTION,
            self::SHORT_DESCRIPTION,
            self::URL_KEY,
        ],
        'meta_data' => [
            self::META_TITLE,
            self::META_TAGS,
            self::META_DESCRIPTION,
            self::META_ROBOTS
        ]
    ];

    public const SOCIAL_LINK_AS_METHODS = [
        self::FACEBOOK_PROFILE => 'getFacebookProfile',
        self::TWITTER_PROFILE => 'getTwitterProfile',
        self::LINKEDIN_PROFILE => 'getLinkedinProfile',
        self::YOUTUBE_PROFILE => 'getYoutubeProfile',
        self::INSTAGRAM_PROFILE => 'getInstagramProfile',
        self::TIKTOK_PROFILE => 'getTiktokProfile',
    ];

    /**
     * @return int
     */
    public function getAuthorId();

    /**
     * @param int $authorId
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setAuthorId($authorId);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getUrlKey();

    /**
     * @param string|null $urlKey
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string|null $metaTitle
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string|null
     */
    public function getMetaTags();

    /**
     * @param string|null $metaTags
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setMetaTags($metaTags);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param null $name
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function prepapreUrlKey($name = null);

    /**
     * @return string
     */
    public function getFacebookProfile();

    /**
     * @param string $profileLink
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setFacebookProfile($profileLink);

    /**
     * @return string
     */
    public function getTwitterProfile();

    /**
     * @param string $profileLink
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setTwitterProfile($profileLink);

    /**
     * @return string|null
     */
    public function getLinkedinProfile(): ?string;

    /**
     * @param string|null $profileLink
     */
    public function setLinkedinProfile(?string $profileLink): void;

    /**
     * @return string|null
     */
    public function getYoutubeProfile(): ?string;

    /**
     * @param string|null $profileLink
     */
    public function setYoutubeProfile(?string $profileLink): void;

    /**
     * @return string|null
     */
    public function getInstagramProfile(): ?string;

    /**
     * @param string|null $profileLink
     */
    public function setInstagramProfile(?string $profileLink): void;

    /**
     * @return string|null
     */
    public function getTiktokProfile(): ?string;

    /**
     * @param string|null $profileLink
     */
    public function setTiktokProfile(?string $profileLink): void;

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Blog\Api\Data\AuthorInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string|null
     */
    public function getJobTitle(): ?string;

    /**
     * @param string|null $title
     */
    public function setJobTitle(?string $title): void;

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string;

    /**
     * @param string|null $description
     */
    public function setShortDescription(?string $description): void;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void;

    public function getMetaRobots(): ?string;

    public function setMetaRobots(string $metaRobots): void;
}
