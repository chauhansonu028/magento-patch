<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel;

use Amasty\Blog\Model\ConfigProvider;
use Magento\Cms\Block\Block;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Layout;
use Psr\Log\LoggerInterface;

class SummaryViewModel implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Block
     */
    private $block;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Layout
     */
    private $layout;

    public function __construct(
        ConfigProvider $configProvider,
        Block $block,
        Layout $layout,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->block = $block;
        $this->logger = $logger;
        $this->layout = $layout;
    }

    public function getSummaryCmsBlockContent(): string
    {
        try {
            $blockId = $this->configProvider->getSummaryBlockId();

            return $this->getBlockContent($blockId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return '';
    }

    /**
     * @param int $blockId
     * @return string
     */
    private function getBlockContent(int $blockId): string
    {
        if (!$blockId) {
            return '';
        }

        try {
            return $this->layout->createBlock(Block::class)->setBlockId($blockId)->toHtml();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return '';
    }
}
