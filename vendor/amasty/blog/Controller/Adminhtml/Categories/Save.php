<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Categories;

use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Block\Sidebar\Category\TreeRenderer;
use Amasty\Blog\Model\Categories;
use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Amasty\Blog\Controller\Adminhtml\Categories
{
    use SaveTrait;

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data) {
            $id = (int)$this->getRequest()->getParam(CategoryInterface::CATEGORY_ID);

            try {
                $data[CategoryInterface::STORE_ID] = $data[CategoryInterface::STORE_ID] ?: 0;

                if ($id) {
                    $model = $this->getCategoryRepository()->getById($id);
                    $data = $this->retrieveItemContent($data, $model);
                } else {
                    $model = $this->getCategoryRepository()->getCategory();
                }

                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getCategoryId())) {
                    $this->getDataPersistor()->set(Categories::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Category with the same url key already exists'));

                    if ($id) {
                        $redirect->setPath('*/*/edit', ['id' => $id]);
                    } else {
                        $redirect->setPath('*/*/new');
                    }

                    return $redirect;
                }

                if ($model->getParentId() && $model->getParentCategory()->getLevel() + 1 > TreeRenderer::LEVEL_LIMIT) {
                    $this->getMessageManager()->addErrorMessage(
                        __(
                            'You have exceeded the category tree depth which is limited by %1.',
                            TreeRenderer::LEVEL_LIMIT
                        )
                    );

                    if ($id) {
                        $redirect->setPath('*/*/edit', ['id' => $id]);
                    } else {
                        $redirect->setPath('*/*/new');
                    }

                    return $redirect;
                }

                $this->_getSession()->setPageData($model->getData());
                $urlKeyUseDefault = $this->getUseDefaults()[CategoryInterface::URL_KEY] ?? false;

                if (!$model->getUrlKey() && !$urlKeyUseDefault) {
                    $model->setUrlKey($this->getUrlHelper()->generate($model->getName()));
                }

                $this->getCategoryRepository()->save($model);
                $this->getMessageManager()->addSuccessMessage(__('You saved the item.'));
                $this->_getSession()->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $redirect->setPath('*/*/edit', [
                        'id' => $model->getCategoryId(),
                        'store' => (int)$this->getRequest()->getParam('store_id', 0)
                    ]);

                    return $redirect;
                }

                $redirect->setPath('*/*/');

                return $redirect;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());
                $this->getDataPersistor()->set(Categories::PERSISTENT_NAME, $data);

                if ($id) {
                    $redirect->setPath('*/*/edit', ['id' => $id]);
                } else {
                    $redirect->setPath('*/*/new');
                }

                return $redirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());
                $this->_getSession()->setPageData(null);
                $this->getDataPersistor()->set(Categories::PERSISTENT_NAME, $data);

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
                $redirect->setPath('*/*/edit', ['category_id' => $id]);

                return $redirect;
            }
        }

        return $redirect->setPath('*/*/');
    }

    /**
     * @return array
     */
    private function getFieldsByStore()
    {
        return CategoryInterface::FIELDS_BY_STORE;
    }

    protected function getRepository(): CategoryRepositoryInterface
    {
        return $this->getCategoryRepository();
    }
}
