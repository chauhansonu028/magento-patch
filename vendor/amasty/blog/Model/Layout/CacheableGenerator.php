<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout;

use Magento\Framework\Cache\FrontendInterface;

class CacheableGenerator implements GeneratorInterface
{
    const CACHE_LIFE_TIME = 86400;
    const CACHE_TAG = 'amasty_blog_layout';

    /**
     * @var GeneratorInterface
     */
    private $xmlGenerator;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cacheLifeTime;

    public function __construct(
        GeneratorInterface $xmlGenerator,
        FrontendInterface $cache,
        int $cacheLifeTime = self::CACHE_LIFE_TIME
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->cache = $cache;
        $this->cacheLifeTime = $cacheLifeTime;
    }

    public function generate(Config $layoutConfig): string
    {
        $cacheKey = $layoutConfig->getCacheKey();
        $result = $this->cache->load($cacheKey);

        if (empty($result)) {
            $result = $this->xmlGenerator->generate($layoutConfig);
            $this->cache->save(
                $result,
                $cacheKey,
                [self::CACHE_TAG, $layoutConfig->getLayoutName(), $layoutConfig->getConfigIdentifier()],
                $this->cacheLifeTime
            );
        }

        return $result;
    }
}
