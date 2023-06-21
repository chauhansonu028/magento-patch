<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class FilterableAttributesCache
{
    /**
     * Cache key for store label attribute
     */
    private const CACHE_PREFIX = 'LIVE_SEARCH_FILTERABLE_ATTRIBUTE_CODES';

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var StateInterface
     */
    private StateInterface $cacheState;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $cacheState,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
    }

    /**
     * Load filterable attribute codes from cache.
     *
     * @return array|null
     */
    public function load(): ?array
    {
        $cacheKey = self::CACHE_PREFIX;
        if ($this->isCacheEnabled() && ($filterableAttributeCodes = $this->cache->load($cacheKey))) {
            return $this->serializer->unserialize($filterableAttributeCodes);
        }
        return null;
    }

    /**
     * Store filterable attribute codes to cache.
     *
     * @param array $filterableAttributeCodes
     */
    public function save(array $filterableAttributeCodes): void
    {
        $cacheKey = self::CACHE_PREFIX;
        if ($this->isCacheEnabled()) {
            $this->cache->save(
                $this->serializer->serialize($filterableAttributeCodes),
                $cacheKey,
                [
                    Type::CACHE_TAG,
                    EntityAttribute::CACHE_TAG
                ]
            );
        }
    }

    /**
     * Check if cache is enabled
     *
     * @return bool
     */
    private function isCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER);
    }
}
