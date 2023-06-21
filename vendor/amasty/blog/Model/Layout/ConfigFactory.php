<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout;

use Amasty\Base\Model\Serializer;
use Amasty\Blog\Exceptions\LayoutConfigValidationException;
use Amasty\Blog\Model\Source\Layout as LayoutSource;
use Magento\Framework\ObjectManagerInterface;

class ConfigFactory
{
    const LAYOUT = 'layout';
    const LEFT_SIDE = 'left_side';
    const RIGHT_SIDE = 'right_side';
    const CONTENT = 'content';

    const REQUIRED_FIELDS = [
        self::LAYOUT,
        self::LEFT_SIDE,
        self::RIGHT_SIDE,
        self::CONTENT
    ];

    const DI_NAMES_MAP = [
      self::LEFT_SIDE => 'leftSideBlocks',
      self::RIGHT_SIDE => 'rightSideBlocks',
      self::CONTENT => 'contentBlocks'
    ];

    const LAYOUT_NAMES_MAP = [
        LayoutSource::ONE_COLUMN_LAYOUT => 'amasty_blog_one_column_layout',
        LayoutSource::TWO_COLUMNS_LEFT_LAYOUT => 'amasty_blog_two_column_left_layout',
        LayoutSource::TWO_COLUMNS_RIGHT_LAYOUT => 'amasty_blog_two_column_right_layout',
        LayoutSource::THREE_COLUMNS_LAYOUT => 'amasty_blog_three_column_layout'
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string[]
     */
    private $blockAliasesMap;

    /**
     * @var BlockConfigFactory
     */
    private $blockConfigFactory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        BlockConfigFactory $blockConfigFactory,
        Serializer $serializer,
        array $blockAliasesMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->blockAliasesMap = $blockAliasesMap;
        $this->blockConfigFactory = $blockConfigFactory;
    }

    /**
     * Builds model from json config
     *
     * @param string $jsonConfig
     * @return Config
     * @throws LayoutConfigValidationException
     * @example {
     *             "layout": "two-columns-right",
     *             "left_side": [],
     *             "right_side": [
     *                              "categories",
     *                              "tags",
     *                              "search",
     *                              "recent-comments",
     *                              "recent-posts"
     *                           ],
     *              "content": ["list"]
     *          }
     *
     */
    public function fromJsonConfig(string $jsonConfig, string $identifier): Config
    {
        $config = $this->parseJsonConfig($jsonConfig);
        $buildData = [];

        if (!$this->isConfigValid($config)) {
            throw new LayoutConfigValidationException(__('Invalid layout config. Required parameters are missing.'));
        }

        $buildData['layoutName'] = $this->getLayoutName($config);
        $buildData['configIdentifier'] = $identifier;

        foreach ([self::RIGHT_SIDE, self::LEFT_SIDE, self::CONTENT] as $contentArea) {
            $buildData[self::DI_NAMES_MAP[$contentArea]] = $this->parseContentBlocksConfig(
                $config[$contentArea],
                $contentArea
            );
        }

        return $this->create($buildData);
    }

    /**
     * @param array $config
     * @return string
     * @throws LayoutConfigValidationException
     */
    private function getLayoutName(array $config): string
    {
        $layoutName = $config[self::LAYOUT];

        if (!isset(self::LAYOUT_NAMES_MAP[$layoutName])) {
            throw new LayoutConfigValidationException(__('Invalid layout name'));
        }

        return self::LAYOUT_NAMES_MAP[$layoutName];
    }

    private function parseJsonConfig(string $jsonConfig): array
    {
        try {
            return $this->serializer->unserialize($jsonConfig);
        } catch (\Exception $e) {
            throw new LayoutConfigValidationException(__('Layout config format broken'));
        }
    }

    private function isConfigValid(array $config): bool
    {
        return count(array_diff(array_keys($config), self::REQUIRED_FIELDS)) === 0;
    }

    /**
     * @param string[] $blocksAliases
     * @return string[]
     */
    private function parseContentBlocksConfig(array $blocksAliases, string $sectionName): array
    {
        return array_map(function (string $blockAlias) use ($sectionName): BlockConfig {
            if (!isset($this->blockAliasesMap[$blockAlias])) {
                throw new LayoutConfigValidationException(__('Invalid layout name.'));
            }

            if (!is_array($this->blockAliasesMap[$blockAlias])) {
                $config['className'] = $this->blockAliasesMap[$blockAlias];
            } else {
                $config = $this->blockAliasesMap[$blockAlias];
            }

            $config['sectionName'] = $sectionName;

            return $this->blockConfigFactory->create($config);
        }, $blocksAliases);
    }

    /**
     * Provides LayoutConfig model
     *
     * @param array $data
     * @return Config
     */
    public function create(array $data): Config
    {
        return $this->objectManager->create(Config::class, $data);
    }
}
