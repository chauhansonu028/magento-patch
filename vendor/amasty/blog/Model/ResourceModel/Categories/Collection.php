<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Categories;

use Amasty\Blog\Api\Data\CategoryInterface;
use Amasty\Blog\Model\ResourceModel\Categories;
use Amasty\Blog\Model\ResourceModel\Traits\CollectionTrait;
use Amasty\Blog\Model\ResourceModel\Traits\StoreCollectionTrait;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Store\Model\Store;

class Collection extends \Amasty\Blog\Model\ResourceModel\Abstracts\Collection
{
    use StoreCollectionTrait;
    use CollectionTrait;

    public const CATEGORY_POST_RELATION_TABLE = 'amasty_blog_posts_category';

    public const MULTI_STORE_FIELDS_MAP = [
        CategoryInterface::STATUS => 'IFNULL(noDefaultStore.status, store.status)',
        CategoryInterface::NAME => 'IFNULL(noDefaultStore.name, store.name)',
        CategoryInterface::META_TITLE => 'IFNULL(noDefaultStore.meta_title, store.meta_title)',
        CategoryInterface::META_DESCRIPTION => 'IFNULL(noDefaultStore.meta_description, store.meta_description)',
        CategoryInterface::META_TAGS => 'IFNULL(noDefaultStore.meta_tags, store.meta_tags)',
        CategoryInterface::META_ROBOTS => 'IFNULL(noDefaultStore.meta_robots, store.meta_robots)',
        CategoryInterface::URL_KEY => 'IFNULL(noDefaultStore.url_key, store.url_key)',
        CategoryInterface::DESCRIPTION => 'IFNULL(noDefaultStore.description, store.description)',
    ];

    /**
     * @var string
     */
    protected $_idFieldName = 'category_id';

    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'category_id' => 'main_table.category_id'
        ]
    ];

    public function _construct()
    {
        $this->_init(
            \Amasty\Blog\Model\Categories::class,
            Categories::class
        );
    }

    /**
     * @param $direction
     * @return $this
     */
    public function setSortOrder($direction)
    {
        $this->getSelect()->order("main_table.sort_order {$direction}");

        return $this;
    }

    /**
     * @param $postId
     *
     * @return $this
     */
    public function addPostFilter($postId)
    {
        $postTable = $this->getTable(self::CATEGORY_POST_RELATION_TABLE);

        $this->getSelect()
            ->join(['post' => $postTable], "post.category_id = main_table.category_id", [])
            ->where("post.post_id = ?", $postId);

        return $this;
    }

    /**
     * @param array $categoryIds
     * @return $this
     */
    public function addIdFilter($categoryIds = [])
    {
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }
        $this->addFieldToFilter('category_id', ['in' => $categoryIds]);

        return $this;
    }

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        parent::renderFilters();
        if ($this->getQueryText()) {
            $this->getSelect()->group('main_table.category_id');
        }
    }

    /**
     * @param array $stores
     *
     * @return array
     */
    public function getPostsCount($stores)
    {
        $select = $this->getConnection()->select()
            ->from(
                ['posts_cat' => $this->getTable('amasty_blog_posts_category')],
                ['category' => 'posts_cat.category_id', 'posts_count' => 'COUNT(posts_cat.category_id)']
            )->join(
                ['posts' => $this->getTable('amasty_blog_posts')],
                'posts.post_id = posts_cat.post_id',
                []
            )->join(
                ['posts_store' => $this->getTable('amasty_blog_posts_store')],
                'posts_store.post_id = posts.post_id',
                []
            )->where(
                'posts_store.store_id IN (?) OR posts_store.store_id = ' . Store::DEFAULT_STORE_ID,
                $stores
            )->where('posts_store.status = ?', [PostStatus::STATUS_ENABLED])
            ->group('category');

        return $this->getConnection()->fetchPairs($select);
    }

    public function addStatusFilter(int $status): self
    {
        $this->getSelect()->where(self::MULTI_STORE_FIELDS_MAP[CategoryInterface::STATUS] . '=' . $status);

        return $this;
    }

    public function getStoreTable(): string
    {
        return $this->getTable(Categories::STORE_TABLE_NAME);
    }
}
