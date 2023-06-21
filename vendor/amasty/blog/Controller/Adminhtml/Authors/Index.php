<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Authors;

/**
 * Class
 */
class Index extends \Amasty\Blog\Controller\Adminhtml\Authors
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getPageFactory()->create();
        $resultPage->setActiveMenu('Amasty_Blog::authors');
        $resultPage->getConfig()->getTitle()->prepend(__('Authors'));
        $resultPage->addBreadcrumb(__('Authors'), __('Authors'));

        return $resultPage;
    }
}
