<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Tags;

use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Amasty\Blog\Model\Tag;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Amasty\Blog\Controller\Adminhtml\Tags
{
    use SaveTrait;

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();
            $id = (int)$this->getRequest()->getParam('tag_id');
            try {
                if ($id) {
                    $model = $this->getTagRepository()->getById($id);
                    $data = $this->retrieveItemContent($data, $model);
                } else {
                    $model = $this->getTagRepository()->getTagModel();
                }

                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getTagId())) {
                    $this->getDataPersistor()->set(Tag::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Tag with the same url key already exists'));

                    if ($id) {
                        $redirect->setPath('*/*/edit', ['id' => $id]);
                    } else {
                        $redirect->setPath('*/*/new');
                    }

                    return $redirect;
                }

                $this->_getSession()->setPageData($model->getData());
                $this->getTagRepository()->save($model);
                $this->getMessageManager()->addSuccessMessage(__('You saved the item.'));
                $this->_getSession()->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $redirect->setPath('*/*/edit', [
                        'id' => $model->getId(),
                        'store' => (int)$this->getRequest()->getParam('store_id', 0)
                    ]);

                    return $redirect;
                }

                $redirect->setPath('*/*/');

                return $redirect;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());
                $this->getDataPersistor()->set(Tag::PERSISTENT_NAME, $data);

                if ($id) {
                    $redirect->setPath('*/*/edit', ['id' => $id]);
                } else {
                    $redirect->setPath('*/*/new');
                }

                return $redirect;
            } catch (\Exception $e) {
                $this->getMessageManager()->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->getLogger()->critical($e);
                $this->_getSession()->setPageData($data);
                $redirect->setPath('*/*/edit', ['id' => $id]);

                return $redirect;
            }
        }

        return $redirect->setPath('*/*/');
    }

    protected function getRepository(): TagRepositoryInterface
    {
        return $this->getTagRepository();
    }

    /**
     * @return array
     */
    private function getFieldsByStore()
    {
        return TagInterface::FIELDS_BY_STORE;
    }
}
