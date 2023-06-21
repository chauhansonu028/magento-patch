<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Amasty\Blog\Model\Cache\Type\Blog;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EnableBlogCache implements DataPatchInterface
{
    /**
     * @var StateInterface
     */
    private $cacheState;

    public function __construct(
        StateInterface $cacheState
    ) {
        $this->cacheState = $cacheState;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->cacheState->setEnabled(Blog::TYPE_IDENTIFIER, true);

        return $this;
    }
}
