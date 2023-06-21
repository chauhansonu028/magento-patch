<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\DataProviders;

interface FileScopeDataInterface
{
    /**
     * @param array $params
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface|\Amasty\ProductAttachment\Api\Data\FileInterface[]|array
     */
    public function execute($params);
}
