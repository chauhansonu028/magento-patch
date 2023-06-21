<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\SaveProcessors;

interface FileScopeSaveProcessorInterface
{
    /**
     * @param array $params
     *
     * @return array
     */
    public function execute($params);
}
