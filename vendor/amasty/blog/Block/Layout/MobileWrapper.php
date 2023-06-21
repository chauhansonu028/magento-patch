<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Layout;

use Amasty\Blog\Model\Layout\BlockConfig;
use Amasty\Blog\ViewModel\Layout\Wrapper;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class MobileWrapper extends Template
{
    const LEFT_SIDE = 'left';
    const RIGHT_SIDE = 'right';
    const ADDITIONAL_BLOCKS_CONTAINER = 'additional.sidebar';
    const WRAPPER_BLOCK_NAME = 'amasty.blog.widget_wrapper';

    private $blocksSide = self::LEFT_SIDE;

    /**
     * @var Wrapper
     */
    private $wrapperViewModel;

    public function __construct(
        Context $context,
        Wrapper $wrapperViewModel,
        array $data = []
    ) {
        $this->wrapperViewModel = $wrapperViewModel;

        parent::__construct($context, $data);
    }

    public function setRightSideBlocks(): void
    {
        $this->blocksSide = self::RIGHT_SIDE;
    }

    public function getBlocksSide(): string
    {
        return $this->blocksSide;
    }

    /**
     * @return string[]
     */
    public function getChildBlocksHtml(): iterable
    {
        foreach ($this->getChildNames() as $childName) {
            if ($childName !== self::ADDITIONAL_BLOCKS_CONTAINER) {
                $blockHtml = trim((string) $this->getChildHtml($childName));
                $widgetBlockName = $this->getWidgetBlockName($childName);

                if ($blockHtml) {
                    yield $this->wrapperViewModel->getBlockIdentifierByNameInLayout($widgetBlockName) => $blockHtml;
                }
            }
        }
    }

    public function getWidgetBlockName(string $childName): string
    {
        if (strpos($childName, self::WRAPPER_BLOCK_NAME) === 0) {
            $childBlock = $this->getChildBlock($childName)->getChildBlock(BlockConfig::DEFAULT_ALIAS);

            if ($childBlock) {
                $childName = $childBlock->getNameInLayout();
            }
        }

        return $childName;
    }

    public function getSwipePhrase(): Phrase
    {
        return $this->getBlocksSide() === self::LEFT_SIDE ? __('Swipe to the left') : __('Swipe to the right');
    }
}
