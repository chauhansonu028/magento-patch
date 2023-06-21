<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\DataProvider;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Repository\PostRepository;
use Amasty\Blog\Model\ResourceModel\Posts\Collection\GridFactory as CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class RelatedPostsProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PostInterface
     */
    private $post;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PostRepository $postRepository,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collection = $collectionFactory->create();
        $this->postRepository = $postRepository;
        $this->request = $request;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();

        if ($this->getPost()) {
            $collection->addFieldToFilter(
                $collection->getIdFieldName(),
                ['nin' => [$this->getPost()->getPostId()]]
            );
        }

        $items = [];
        /** @var PostInterface $post **/
        foreach ($collection->getItems() as $post) {
            $items[] = $this->fillData($post);
        }

        $data = [
            'totalRecords' => $collection->getSize(),
            'items' => $items
        ];

        return $data;
    }

    /**
     * @param PostInterface $post
     *
     * @return array
     */
    protected function fillData(PostInterface $post)
    {
        return [
            'post_id' => $post->getPostId(),
            'post_thumbnail' => $post->getListThumbnailSrc(),
            'title' => $post->getTitle(),
            'url_key' => $post->getUrlKey(),
            'status' => $post->getStatus()
        ];
    }

    /**
     * Retrieve posp
     *
     * @return PostInterface|null
     */
    protected function getPost()
    {
        if (null !== $this->post) {
            return $this->post;
        }

        if (!($id = $this->request->getParam('post_id'))) {
            return null;
        }

        return $this->post = $this->postRepository->getById($id);
    }
}
