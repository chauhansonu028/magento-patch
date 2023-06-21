<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\DataProvider;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Controller\Adminhtml\Authors\Edit;
use Amasty\Blog\Model\Author;
use Amasty\Blog\Model\BlogRegistry;
use Amasty\Blog\Model\DataProvider\Traits\DataProviderTrait;
use Amasty\Blog\Model\ImageProcessor;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class AuthorDataProvider extends AbstractDataProvider
{
    use DataProviderTrait;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Blog\Api\AuthorRepositoryInterface
     */
    private $repository;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var BlogRegistry
     */
    private $blogRegistry;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        \Amasty\Blog\Api\AuthorRepositoryInterface $repository,
        PoolInterface $pool,
        BlogRegistry $blogRegistry,
        ImageProcessor $imageProcessor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;
        $this->collection = $repository->getAuthorCollection();
        $this->pool = $pool;
        $this->blogRegistry = $blogRegistry;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $data = parent::getData();

        $storeId = $this->blogRegistry->registry(AbstractModifier::CURRENT_STORE_ID) ?: 0;
        $current = $this->blogRegistry->registry(Edit::CURRENT_AMASTY_BLOG_AUTHOR);
        $data = $this->prepareData($current, $storeId, $data);
        if ($current) {
            $data = $this->modifyData($current, $data);
        }

        if ($savedData = $this->dataPersistor->get(Author::PERSISTENT_NAME)) {
            $savedAuthorId = isset($savedData['author_id']) ? $savedData['author_id'] : null;
            $data[$savedAuthorId] = isset($data[$savedAuthorId])
                ? array_merge($data[$savedAuthorId], $savedData)
                : $savedData;
            $this->dataPersistor->clear(Author::PERSISTENT_NAME);
        }

        return $data;
    }

    protected function modifyData(AuthorInterface $model, array $data): array
    {
        $image = $model->getImage();
        if ($image) {
            $data[$model->getId()][AuthorInterface::IMAGE] = [
                [
                    'name' => $image,
                    'url'  => $this->imageProcessor->getThumbnailUrl($image)
                ]
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getFieldsByStore()
    {
        return AuthorInterface::FIELDS_BY_STORE;
    }
}
