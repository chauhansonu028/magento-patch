<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\LiveSearch;

use Amasty\Blog\Model\ConfigProvider;

class LiveSearchPool
{
    /**
     * @var LiveSearchInterface[]
     */
    private $liveSearchEntities;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        array $liveSearchEntities = []
    ) {
        $this->liveSearchEntities = $liveSearchEntities;
        $this->configProvider = $configProvider;
    }

    public function search(string $query): array
    {
        $result = [];
        foreach ($this->liveSearchEntities as $key => $liveSearchEntity) {
            $searchResult = $liveSearchEntity->getSearchResult($query, $this->configProvider->getItemsPerEntity());
            if ($searchResult) {
                $key = __($key)->render();
                $result[$key] = $searchResult;
            }
        }

        return $result;
    }
}
