<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\ContentPreparation;

use Amasty\Blog\ViewModel\ContentPreparation\Preparers\PreparerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CompositePreparator implements ArgumentInterface, PreparerInterface
{
    /**
     * @var PreparerInterface[]
     */
    private $contentPreparers;

    public function __construct(
        array $contentPreparers = []
    ) {
        $this->contentPreparers = $contentPreparers;
    }

    public function prepare(string $content): string
    {
        foreach ($this->contentPreparers as $preparator) {
            if ($preparator instanceof PreparerInterface) {
                $content = $preparator->prepare($content);
            }
        }

        return $content;
    }
}
