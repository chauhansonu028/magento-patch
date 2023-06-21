<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\LiveSearch;

interface LiveSearchInterface
{
    public function getSearchResult(string $query, int $itemsLimit): array;
}
