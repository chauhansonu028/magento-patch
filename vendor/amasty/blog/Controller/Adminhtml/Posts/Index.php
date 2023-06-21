<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Posts;

/**
 * Class Index
 */
class Index extends \Amasty\Blog\Controller\Adminhtml\Posts
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getPageFactory()->create();
        $resultPage->setActiveMenu('Amasty_Blog::posts');
        $resultPage->getConfig()->getTitle()->prepend(__('Posts'));
        $resultPage->addBreadcrumb(__('Posts'), __('Posts'));

        return $resultPage;
    }
}
