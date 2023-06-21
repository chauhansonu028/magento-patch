<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\SkuGroup;

use Digitalriver\DrPay\Api\SkuGroupProviderInterface;
use Digitalriver\DrPay\Helper\Config;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class SkuGroupManagement
 *
 * Provides caching and high level functionality for a SKU Group list
 */
class DataProviderCached implements SkuGroupProviderInterface
{
    private const CACHE_KEY = 'dr_sku_groups_all';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SkuGroupProviderInterface
     */
    private $skuGroupProvider;

    /**
     * @var array|null   SKU group list cached in memory
     */
    private $skuGroups = null;

    /**
     * DataProviderCached constructor.
     * @param CacheInterface $cache
     * @param Config $config
     * @param SkuGroupProviderInterface $skuGroupProvider
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        Config $config,
        SkuGroupProviderInterface $skuGroupProvider,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->skuGroupProvider = $skuGroupProvider;
    }

    /**
     * Returns a list of all available sku groups
     *
     * @return array
     */
    public function getSkuGroups(): array
    {
        if (($this->skuGroups === null) && ($this->skuGroups = $this->loadFromCache()) === null) {
            // The SKU groups were not in memory nor in cache storage, loading from the source
            $this->skuGroups = $this->skuGroupProvider->getSkuGroups();
            $this->saveToCache($this->skuGroups);
        }
        return $this->skuGroups ?? [];
    }

    /**
     * @return array|null
     */
    private function loadFromCache(): ?array
    {
        if ($cachedGroups = $this->cache->load(self::CACHE_KEY)) {
            try {
                return $this->serializer->unserialize($cachedGroups);
            } catch (\Exception $e) {
                // Do nothing, return null will cause the value to be reloaded and overwritten
            }
        }
        return null;
    }

    /**
     * @param array $skuGroups
     */
    private function saveToCache(array $skuGroups): void
    {
        $this->cache->save(
            $this->serializer->serialize($skuGroups),
            self::CACHE_KEY,
            [],
            $this->config->getSkuGroupTTL()
        );
    }
}
