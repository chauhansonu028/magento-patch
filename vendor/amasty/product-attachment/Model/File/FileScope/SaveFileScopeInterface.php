<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope;

interface SaveFileScopeInterface
{
    /**
     * @param array $params
     * @param string $saveProcessorName
     *
     * @return void
     */
    public function execute($params, $saveProcessorName);
}
