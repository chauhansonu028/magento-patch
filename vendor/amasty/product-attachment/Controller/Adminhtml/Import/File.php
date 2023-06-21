<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Block\Adminhtml\Steps;
use Amasty\ProductAttachment\Controller\Adminhtml\Import;
use Magento\Framework\Controller\ResultFactory;

class File extends Import
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_ProductAttachment::import');
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Your Attachments'));
        /** @var Steps $steps */
        $steps = $resultPage->getLayout()->getBlock('import-steps');
        $steps->setCurrentStep(Steps::STEP1)
            ->setNextLink($this->getUrl('amfile/import/save'));

        return $resultPage;
    }
}
