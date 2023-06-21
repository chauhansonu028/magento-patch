<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Comments;

/**
 * Class Reply
 */
class Reply extends \Amasty\Blog\Controller\Adminhtml\Comments
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->_redirect('*/*/edit', ['reply_to_id' => $this->getRequest()->getParam('id')]);
    }
}
