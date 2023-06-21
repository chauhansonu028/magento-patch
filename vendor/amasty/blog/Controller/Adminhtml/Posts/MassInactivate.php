<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\Controller\Result\Redirect;

class MassInactivate extends AbstractMassAction
{
    /**
     * @param PostInterface $post
     * @return Redirect
     */
    protected function itemAction($post)
    {
        try {
            $this->getRepository()->changeStatus($post, PostStatus::STATUS_DISABLED);
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
