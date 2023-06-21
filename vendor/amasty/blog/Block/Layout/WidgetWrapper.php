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
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class WidgetWrapper extends Template
{
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

    public function getWrappedBlock(): ?AbstractBlock
    {
        return $this->getChildBlock(BlockConfig::DEFAULT_ALIAS) ?: null;
    }

    public function getWrappedBlockIdentifier(): string
    {
        $childBlock = $this->getWrappedBlock();

        return $childBlock
            ? $this->wrapperViewModel->getBlockIdentifierByNameInLayout($childBlock->getNameInLayout())
            : uniqid();
    }
}
