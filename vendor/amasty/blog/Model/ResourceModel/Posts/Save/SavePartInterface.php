<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts\Save;

use Amasty\Blog\Model\Posts;

interface SavePartInterface
{
    public function execute(Posts $model): void;
}
