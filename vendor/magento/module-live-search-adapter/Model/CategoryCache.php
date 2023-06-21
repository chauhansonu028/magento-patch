<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CategoryCache
{
    /**
     * Cache key for store label attribute
     */
    private const CACHE_PREFIX = 'LIVE_SEARCH_CATEGORY_DATA';

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @codeCoverageIgnore
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * Load category from cache
     *
     * @param string $key
     * @return array|null
     */
    public function load(string $key): ?array
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        if ($categoryData = $this->cache->load($cacheKey)) {
            return $this->serializer->unserialize($categoryData);
        }
        return null;
    }

    /**
     * Save category to cache
     *
     * @param string $key
     * @param array $categoryData
     */
    public function save(string $key, array $categoryData): void
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        $this->cache->save(
            $this->serializer->serialize($categoryData),
            $cacheKey,
            [
                Category::CACHE_TAG
            ]
        );
    }
}
