<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\ViewModel\Attachment\Renderer;

interface ProcessorInterface
{
    public function process(array $data): array;
}
