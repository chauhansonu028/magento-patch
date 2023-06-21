<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Controller\Adminhtml\Import;
use Magento\Framework\Controller\ResultFactory;

class Index extends Import
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_ProductAttachment::import');
        $resultPage->addBreadcrumb(__('Product Attachments'), __('Product Attachments'));
        $resultPage->addBreadcrumb(__('Mass Attachment Import'), __('Mass Attachment Import'));
        $resultPage->getConfig()->getTitle()->prepend(__('Mass Attachment Import'));

        return $resultPage;
    }
}
