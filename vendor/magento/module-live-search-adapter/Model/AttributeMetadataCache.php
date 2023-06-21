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

/**
 * Cache for attributes metadata.
 */
class AttributeMetadataCache
{
    /**
     * Cache key for store label attribute
     */
    private const CACHE_PREFIX = 'LIVE_SEARCH_ATTRIBUTE_METADATA';

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
     * @codeCoverageIgnore
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
     * Load attribute metadata from cache.
     *
     * @param string $attributeCode
     * @return array|null
     */
    public function load(string $attributeCode): ?array
    {
        $cacheKey = self::CACHE_PREFIX . $attributeCode;
        if ($this->isCacheEnabled() && ($attributeOptions = $this->cache->load($cacheKey))) {
            return $this->serializer->unserialize($attributeOptions);
        }
        return null;
    }

    /**
     * Save attribute metadata to cache.
     *
     * @param string $attributeCode
     * @param array $attributeOptions
     */
    public function save(string $attributeCode, array $attributeOptions): void
    {
        $cacheKey = self::CACHE_PREFIX . $attributeCode;
        if ($this->isCacheEnabled()) {
            $this->cache->save(
                $this->serializer->serialize($attributeOptions),
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
