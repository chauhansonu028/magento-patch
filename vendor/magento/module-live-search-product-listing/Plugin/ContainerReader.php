<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchProductListing\Plugin;

use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Container;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\LiveSearchProductListing\Model\LayoutElementsRemover;

/**
 * Plugin for layout containers reader to change elements visibility depends on Live Search admin configuration.
 */
class ContainerReader
{
    /**
     * @var string[][]
     */
    private array $containersToRemove;

    /**
     * @var LayoutElementsRemover
     */
    private LayoutElementsRemover $layoutElementsRemover;

    /**
     * @param LayoutElementsRemover $layoutElementsRemover
     * @param string[] $containersToRemove
     */
    public function __construct(
        LayoutElementsRemover $layoutElementsRemover,
        array $containersToRemove
    ) {
        $this->containersToRemove = $containersToRemove;
        $this->layoutElementsRemover = $layoutElementsRemover;
    }

    /**
     * Mark specific container as removed if configuration in admin active for specific Live Search feature.
     *
     * @param Container $subject
     * @param Context $readerContext
     * @param Element $currentElement
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInterpret(Container $subject, Context $readerContext, Element $currentElement): array
    {
        $this->layoutElementsRemover->removeLayoutElements(
            $currentElement,
            $this->containersToRemove,
            \get_class($this)
        );
        return [$readerContext, $currentElement];
    }
}
