<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Comments;

/**
 * Class
 */
class Index extends \Amasty\Blog\Controller\Adminhtml\Comments
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getPageFactory()->create();
        $resultPage->setActiveMenu('Amasty_Blog::comments');
        $resultPage->getConfig()->getTitle()->prepend(__('Comments'));
        $resultPage->addBreadcrumb(__('Comments'), __('Comments'));

        return $resultPage;
    }
}
