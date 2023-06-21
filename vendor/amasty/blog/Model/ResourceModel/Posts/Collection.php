<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Posts as PostModel;
use Amasty\Blog\Model\ResourceModel\Author\CollectionFactory as AuthorCollectionFactory;
use Amasty\Blog\Model\ResourceModel\Posts;
use Amasty\Blog\Model\ResourceModel\Posts as PostResource;
use Amasty\Blog\Model\ResourceModel\Traits\CollectionTrait;
use Amasty\Blog\Model\ResourceModel\Traits\StoreCollectionTrait;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper\Mysql\Fulltext as FulltextQueryGenerator;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

class Collection extends \Amasty\Blog\Model\ResourceModel\Abstracts\Collection
{
    use StoreCollectionTrait;

    use CollectionTrait {
        getFulltextIndexColumns as protected;
    }

    public const MIN_FULLTEXT_SEARCH_QUERY_LENGTH = 3;

    public const MULTI_STORE_FIELDS_MAP = [
        PostInterface::TITLE => 'IFNULL(noDefaultStore.title, store.title)',
        PostInterface::URL_KEY => 'IFNULL(noDefaultStore.url_key, store.url_key)',
        PostInterface::STATUS => 'IFNULL(noDefaultStore.status, store.status)',
        PostInterface::PUBLISHED_AT => 'IFNULL(noDefaultStore.published_at, store.published_at)',
        PostInterface::META_TITLE => 'IFNULL(noDefaultStore.meta_title, store.meta_title)',
        PostInterface::META_DESCRIPTION => 'IFNULL(noDefaultStore.meta_description, store.meta_description)',
        PostInterface::META_TAGS => 'IFNULL(noDefaultStore.meta_tags, store.meta_tags)',
        PostInterface::META_ROBOTS => 'IFNULL(noDefaultStore.meta_robots, store.meta_robots)',
        PostInterface::CANONICAL_URL => 'IFNULL(noDefaultStore.canonical_url, store.canonical_url)',
        PostInterface::POST_THUMBNAIL_ALT => 'IFNULL(noDefaultStore.post_thumbnail_alt, store.post_thumbnail_alt)',
        PostInterface::LIST_THUMBNAIL_ALT => 'IFNULL(noDefaultStore.list_thumbnail_alt, store.list_thumbnail_alt)',
        PostInterface::SHORT_CONTENT => 'IFNULL(noDefaultStore.short_content, store.short_content)',
        PostInterface::FULL_CONTENT => 'IFNULL(noDefaultStore.full_content, store.full_content)',
    ];

    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'post_id' => 'main_table.post_id',
            'status' => 'store.status',
            'published_at' => 'store.published_at',
        ]
    ];

    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';

    /**
     * @var AuthorCollectionFactory
     */
    private $authorCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FulltextQueryGenerator
     */
    private $fulltextHelper;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AuthorCollectionFactory $authorCollectionFactory,
        StoreManagerInterface $storeManager,
        FulltextQueryGenerator $fulltextHelper,
        StringUtils $stringUtils,
        State $state,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->authorCollectionFactory = $authorCollectionFactory;
        $this->storeManager = $storeManager;
        $this->fulltextHelper = $fulltextHelper;
        $this->stringUtils = $stringUtils;
        $this->state = $state;
    }

    public function _construct()
    {
        $this->_init(PostModel::class, PostResource::class);
    }

    public function addStores(): self
    {
        $this->getSelect()
            ->joinLeft(
                ['store' => $this->getTable('amasty_blog_posts_store')],
                'main_table.post_id = store.post_id'
            );

        $this->setIsStoreDataAdded(true);

        return $this;
    }

    protected function addCategories(): self
    {
        $this->getSelect()
            ->joinLeft(
                ['categories' => $this->getTable('amasty_blog_posts_category')],
                'main_table.post_id = categories.post_id',
                []
            );

        return $this;
    }

    public function addTagFilter($tagIds): self
    {
        if (!is_array($tagIds)) {
            $tagIds = [$tagIds];
        }

        $this->getSelect()
            ->joinLeft(
                ['tags' => $this->getTable('amasty_blog_posts_tag')],
                'main_table.post_id = tags.post_id',
                []
            )
            ->where('tags.tag_id IN (?)', $tagIds)
            ->group('main_table.post_id');

        return $this;
    }

    public function loadByQueryText(string $value): self
    {
        $this->getSelect()
            ->where('main_table.full_content LIKE ?', '%' . $value . '%')
            ->orWhere('main_table.title LIKE ?', '%' . $value . '%');

        return $this;
    }

    /**
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            parent::load($printQuery, $logQuery);
            $this->addLinkedTables();
            $this->loadAuthors();
        }

        return $this;
    }

    public function addFilterByStatus(array $statuses = [PostStatus::STATUS_ENABLED]): void
    {
        $storeId = $this->storeManager->getStore()->getId();
        if ($storeId != Store::DEFAULT_STORE_ID) {
            $this->getSelect()->where(
                'noDefaultStore.status IN (?) OR noDefaultStore.status IS NULL AND store.status IN (?)',
                $statuses
            );
        } else {
            $this->addFieldToFilter('store.status', ['in' => $statuses]);
        }
    }

    private function addLinkedTables(): void
    {
        $this->addLinkedData('category', 'category_id', 'categories');
        $this->addLinkedData('store', 'store_id', 'store_id');
        $this->addLinkedData('tag', 'tag_id', 'tag_ids');
    }

    private function addLinkedData(string $linkedTable, string $linkedField, string $fieldName): void
    {
        $connection = $this->getConnection();

        $postId = $this->getColumnValues('post_id');
        $linked = [];
        if (!empty($postId)) {
            $inCond = $connection->prepareSqlCondition('post_id', ['in' => $postId]);
            $select = $connection->select()
                ->from($this->getTable('amasty_blog_posts_' . $linkedTable))->where($inCond);
            $result = $connection->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($linked[$row['post_id']])) {
                    $linked[$row['post_id']] = [];
                }
                $linked[$row['post_id']][] = $row[$linkedField];
            }
        }

        foreach ($this as $item) {
            if (isset($linked[$item->getId()])) {
                $item->setData($fieldName, $linked[$item->getId()]);
            } else {
                $item->setData($fieldName, []);
            }
        }
    }

    private function loadAuthors(): self
    {
        $authorIds = [];
        /**
         * @var PostInterface $post
         */
        foreach ($this->getItems() as $post) {
            $authorIds[] = $post->getAuthorId();
        }

        $collection = $this->authorCollectionFactory->create();
        $collection->addStoreWithDefault((int)$this->storeManager->getStore()->getId());
        $collection->addFieldToFilter(PostInterface::AUTHOR_ID, ['in' => $authorIds]);

        foreach ($this->getItems() as $post) {
            if (!$author = $collection->getItemById($post->getAuthorId())) {
                $author = $collection->getNewEmptyItem();
            }
            $post->setAuthor($author);
        }

        return $this;
    }

    public function setUrlKeyIsNotNull(): self
    {
        $this->getSelect()->where('main_table.url_key != ""');

        return $this;
    }

    public function setDateOrder(): self
    {
        $this->getSelect()->order('IFNULL(noDefaultStore.published_at, store.published_at) DESC');

        return $this;
    }

    public function setPostIdOrder(string $order = Select::SQL_DESC): void
    {
        $this->addOrder(PostInterface::POST_ID, $order);
    }

    /**
     * @param $categoryIds
     *
     * @return $this
     */
    public function addCategoryFilter($categoryIds)
    {
        $categoryIds = is_array($categoryIds) ? $categoryIds : [$categoryIds];

        $categoryTable = $this->getMainTable() . "_category";
        $this->getSelect()
            ->join(['categories' => $categoryTable], 'categories.post_id = main_table.post_id', [])
            ->where('categories.category_id IN (?)', $categoryIds);

        return $this;
    }

    public function addAuthorFilter(?array $authorIds): self
    {
        $authorIds = is_array($authorIds) ? $authorIds : [$authorIds];

        $this->getSelect()
            ->join(['author' => $this->getTable('amasty_blog_author')], 'author.author_id = main_table.author_id', [])
            ->where('author.author_id IN (?)', $authorIds);

        return $this;
    }

    protected function _renderFiltersBefore(): void
    {
        $this->renderFilters();
        if ($this->getQueryText()) {
            $this->getSelect()->group('main_table.post_id');
        }
    }

    public function setLimit(?int $limit): void
    {
        $this->getSelect()->limit($limit);
    }

    /**
     * @throws LocalizedException
     */
    public function getSelectCountSql(): Select
    {
        $this->applyStoreFilter();

        return parent::getSelectCountSql();
    }

    private function addFulltextSearchQuery(array $columnsForSearch, string $searchExpression): void
    {
        $searchExpression = preg_replace('([+\-><\(\)~*\"@]+$)', '', $searchExpression);
        $select = $this->getSelect();
        $select->columns(
            [
                'rel' => new Zend_Db_Expr(
                    $this->fulltextHelper->getMatchQuery(
                        $columnsForSearch,
                        $searchExpression,
                        FulltextQueryGenerator::FULLTEXT_MODE_BOOLEAN
                    )
                )
            ]
        );
        $this->fulltextHelper->match(
            $select,
            $columnsForSearch,
            $searchExpression,
            true,
            FulltextQueryGenerator::FULLTEXT_MODE_BOOLEAN
        );
        $select->order('rel ' . Select::SQL_DESC);
    }

    private function prepareSearchExpression(string $queryText): string
    {
        //prevent extra large query attack
        $queryText = mb_strtolower($this->stringUtils->substr($queryText, 0, 1024));
        $words = array_filter((array)preg_split('@\s+@', $queryText));

        return implode('* ', $words) . '*';
    }

    public function getStoreTable(): string
    {
        return $this->getTable(Posts::STORE_TABLE_NAME);
    }
}
