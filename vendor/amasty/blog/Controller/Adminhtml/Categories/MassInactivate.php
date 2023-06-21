<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Categories;

use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Model\Source\CategoryStatus;

/**
 * Class
 */
class MassInactivate extends AbstractMassAction
{
    /**
     * @param CategoryInterface $category
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function itemAction($category)
    {
        try {
            $category->setStatus(CategoryStatus::STATUS_DISABLED);
            $this->getRepository()->save($category);
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
