<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Authors;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Amasty\Blog\Model\Tag;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Amasty\Blog\Controller\Adminhtml\Authors
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
            $id = (int)$this->getRequest()->getParam('author_id');
            try {
                if ($id) {
                    $model = $this->getAuthorRepository()->getById($id);
                    $data = $this->retrieveItemContent($data, $model);
                } else {
                    $model = $this->getAuthorRepository()->getAuthorModel();
                }

                $this->prepareImage($data);
                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getAuthorId())) {
                    $this->getDataPersistor()->set(Tag::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Author with the same url key already exists'));

                    if ($id) {
                        $redirect->setPath('*/*/edit', ['id' => $id]);
                    } else {
                        $redirect->setPath('*/*/new');
                    }

                    return $redirect;
                }

                $this->_getSession()->setPageData($model->getData());
                $this->getAuthorRepository()->save($model);
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

                return $this->addRedirect($id);
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

    private function prepareImage(array &$data): void
    {
        if (isset($data[AuthorInterface::IMAGE]) && $data[AuthorInterface::IMAGE]) {
            $imagePath = $this->imageProcessor->moveFile($data[AuthorInterface::IMAGE]);
            unset($data[AuthorInterface::IMAGE]);
            if ($imagePath !== null) {
                $data[AuthorInterface::IMAGE] = $imagePath;
            }
        } else {
            $data[AuthorInterface::IMAGE] = null;
        }
    }

    protected function getRepository(): AuthorRepositoryInterface
    {
        return $this->getAuthorRepository();
    }

    /**
     * @return array
     */
    private function getFieldsByStore()
    {
        return AuthorInterface::FIELDS_BY_STORE;
    }
}
