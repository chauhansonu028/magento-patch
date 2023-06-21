<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Plugin;

use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Block;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\LiveSearchProductListing\Model\LayoutElementsRemover;

/**
 * Plugin for layout blocks reader to change elements visibility depends on Live Search admin configuration.
 */
class BlockReader
{
    /**
     * @var string[][]
     */
    private array $blocksToRemove;

    /**
     * @var LayoutElementsRemover
     */
    private LayoutElementsRemover $layoutElementsRemover;

    /**
     * @param LayoutElementsRemover $layoutElementsRemover
     * @param string[] $blocksToRemove
     */
    public function __construct(
        LayoutElementsRemover $layoutElementsRemover,
        array $blocksToRemove
    ) {
        $this->blocksToRemove = $blocksToRemove;
        $this->layoutElementsRemover = $layoutElementsRemover;
    }

    /**
     * Mark specific block as removed if configuration in admin active for specific Live Search feature
     *
     * @param Block $subject
     * @param Context $readerContext
     * @param Element $currentElement
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInterpret(Block $subject, Context $readerContext, Element $currentElement): array
    {
        $this->layoutElementsRemover->removeLayoutElements(
            $currentElement,
            $this->blocksToRemove,
            \get_class($this)
        );
        return [$readerContext, $currentElement];
    }
}
