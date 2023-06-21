<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Block\Adminhtml;

class Import extends \Magento\Backend\Block\Template
{
    public function getGenerateUrl()
    {
        return $this->getUrl('amfile/import/generate', ['import_id' => $this->getRequest()->getParam('import_id')]);
    }

    public function getFinishLink()
    {
        return $this->getUrl('amfile/import/index');
    }
}
