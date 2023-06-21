<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\ResourceModel\Relation;

use Amasty\ProductAttachment\Api\Data\FileInterface;

interface HandlerInterface
{
    public function execute(FileInterface $entity): FileInterface;
}
