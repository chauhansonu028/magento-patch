<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope;

interface FileScopeDataProviderInterface
{
    /**
     * @param array $params
     * @param string $dataProviderName
     *
     * @return mixed
     */
    public function execute($params, $dataProviderName);
}
