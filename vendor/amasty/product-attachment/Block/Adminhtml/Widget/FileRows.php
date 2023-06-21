<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\Widget;

class FileRows extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_ProductAttachment::files_rows.phtml';

    /**
     * @var array
     */
    private $files;

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        if (empty($this->files)) {
            return [];
        }

        return $this->files;
    }
}
