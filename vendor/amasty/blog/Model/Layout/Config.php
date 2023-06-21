<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout;

use Amasty\Base\Model\Serializer;

class Config
{
    /**
     * @var string
     */
    private $layoutName;

    /**
     * @var string[]
     */
    private $cacheKeyInfo = [];

    /**
     * @var BlockConfig[]
     */
    private $leftSideBlocks;

    /**
     * @var BlockConfig[]
     */
    private $rightSideBlocks;

    /**
     * @var BlockConfig[]
     */
    private $contentBlocks;

    /**
     * @var string
     */
    private $configIdentifier;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Serializer $serializer,
        string $layoutName,
        string $configIdentifier,
        array $leftSideBlocks,
        array $rightSideBlocks,
        array $contentBlocks
    ) {
        $this->layoutName = $layoutName;
        $this->leftSideBlocks = $leftSideBlocks;
        $this->rightSideBlocks = $rightSideBlocks;
        $this->contentBlocks = $contentBlocks;
        $this->configIdentifier = $configIdentifier;
        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layoutName;
    }

    /**
     * @return BlockConfig[]
     */
    public function getLeftSideBlocks(): array
    {
        return $this->leftSideBlocks;
    }

    /**
     * @return BlockConfig[]
     */
    public function getRightSideBlocks(): array
    {
        return $this->rightSideBlocks;
    }

    /**
     * @return BlockConfig[]
     */
    public function getContentBlocks(): array
    {
        return $this->contentBlocks;
    }

    public function getConfigIdentifier(): string
    {
        return $this->configIdentifier;
    }

    public function getCacheKey(): string
    {
        $cacheKeyInfo = array_merge($this->cacheKeyInfo, [$this->getConfigIdentifier(), $this->getLayoutName()]);
        sort($cacheKeyInfo);

        return sha1($this->serializer->serialize(array_unique($cacheKeyInfo)));
    }

    public function addCacheKeyInfo(string ...$keyInfoParts): void
    {
        $this->cacheKeyInfo = array_merge($this->cacheKeyInfo, $keyInfoParts);
    }
}
