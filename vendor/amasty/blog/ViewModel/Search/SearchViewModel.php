<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Search;

use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SearchViewModel implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @return int
     */
    public function getMinCharacterLength(): int
    {
        return $this->configProvider->getMinCharacterLength();
    }

    /**
     * @return int
     */
    public function getItemsPerEntity(): int
    {
        return $this->configProvider->getItemsPerEntity();
    }
}
