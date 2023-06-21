<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Amasty\Blog\Model\DataProvider\Traits\Fields;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\ResourceModel\Posts\Save\SavePostProductRelations;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

class Save extends \Amasty\Blog\Controller\Adminhtml\Posts
{
    use SaveTrait;
    use Fields;

    public function execute(): ResultInterface
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = (int)$this->getRequest()->getParam('post_id');

            try {
                if ($id) {
                    $model = $this->getPostRepository()->getById($id);
                    $data = $this->retrieveItemContent($data, $model);
                } else {
                    $model = $this->getPostRepository()->getPost();
                }

                $data = $this->prepareData($data);
                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getPostId())) {
                    $this->getDataPersistor()->set(Posts::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Post with the same url key already exists'));
                    if ($id) {
                        $resultPage->setPath('*/*/edit', ['id' => $id]);
                    } else {
                        $resultPage->setPath('*/*/new');
                    }

                    return $resultPage;
                }

                $this->_getSession()->setPageData($data);
                $this->prepareForSave($model);
                $this->getPostRepository()->save($model);

                $this->getMessageManager()->addSuccessMessage(__('You saved the item.'));
                $this->_getSession()->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $resultPage->setPath('*/*/edit', [
                        'id' => $model->getPostId(),
                        'store' => (int)$this->getRequest()->getParam('store_id', 0)
                    ]);

                    return $resultPage;
                }

                $resultPage->setPath('*/*/');

                return $resultPage;
            } catch (NoSuchEntityException $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());
                $this->getDataPersistor()->set(Posts::PERSISTENT_NAME, $data);
                if ($id) {
                    $resultPage->setPath('*/*/edit', ['id' => $id]);
                } else {
                    $resultPage->setPath('*/*/new');
                }

                return $resultPage;
            } catch (\Exception $e) {
                $this->getMessageManager()->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->getLogger()->critical($e);
                $this->_getSession()->setPageData($data);
                $resultPage->setPath('*/*/edit', ['id' => $id]);

                return $resultPage;
            }
        }

        $resultPage->setPath('*/*/');

        return $resultPage;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareData(array $data)
    {
        if (!isset($data['categories'])) {
            $data['categories'] = [];
        }
        $data['related_posts_container'] = $data['related_posts_container'] ?? [];
        $data[SavePostProductRelations::DATA_SECTION] = $data[SavePostProductRelations::DATA_SECTION] ?? [];

        if (is_array($data['related_posts_container'])) {
            $related = [];

            foreach ($data['related_posts_container'] as $item) {
                $related[] = $item['post_id'];
            }

            $related = implode(',', $related);
            $data['related_post_ids'] = $related;
            unset($data['related_posts_container']);
        }

        return $data;
    }

    /**
     * @param $model
     */
    private function prepareForSave($model)
    {
        $this->prepareImage($model, PostInterface::POST_THUMBNAIL);
        $this->prepareImage($model, PostInterface::LIST_THUMBNAIL);
        $this->prepareStatus($model);

        if (!$model->getUrlKey() && (int)$model->getStoreId() === Store::DEFAULT_STORE_ID) {
            $model->setUrlKey($this->getUrlHelper()->generate($model->getTitle()));
        }

        $this->prepareEditedAt($model);
    }

    private function prepareEditedAt(PostInterface $post): void
    {
        if ($post->getPostId()) {
            $post->setEditedAt(
                $this->getTimezone()->date(null, null, false)->format('Y-m-d H:i:s')
            );
        }
    }

    /**
     * @param PostInterface $model
     */
    public function prepareStatus($model)
    {
        $currentTimestamp = $this->getTimezone()->date()->getTimestamp();
        $publishedDate = $model->getPublishedAt() ? strtotime($model->getPublishedAt()) : null;

        if (in_array($model->getStatus(), [PostStatus::STATUS_ENABLED]) && $publishedDate > $currentTimestamp) {
            $publishedDate = $currentTimestamp;
        }

        $useDefault = $this->getRequest()->getParam('use_default', []);
        if (!isset($useDefault[PostInterface::PUBLISHED_AT]) || $useDefault[PostInterface::PUBLISHED_AT] != 1) {
            $publishedDate = $publishedDate ?: $currentTimestamp;
        }

        $model->setPublishedAt($publishedDate);
    }

    /**
     * @param $model
     * @param $imageName
     */
    private function prepareImage($model, $imageName)
    {
        $fileName = $imageName . '_file';
        $thumbnail = $model->getData($fileName);

        if (isset($thumbnail) && is_array($thumbnail)) {
            if (isset($thumbnail[0]['name']) && isset($thumbnail[0]['tmp_name'])) {
                $model->setData($imageName, $thumbnail[0]['name']);
            }
        } else {
            $model->setThumbnail($imageName, null);
        }
    }

    protected function getRepository(): PostRepositoryInterface
    {
        return $this->getPostRepository();
    }
}
