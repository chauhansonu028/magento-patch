<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Author;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Model\Author;
use Amasty\Blog\Model\ResourceModel\Author as AuthorResource;
use Amasty\Blog\Model\ResourceModel\Traits\CollectionTrait;
use Amasty\Blog\Model\ResourceModel\Traits\StoreCollectionTrait;

class Collection extends \Amasty\Blog\Model\ResourceModel\Abstracts\Collection
{
    use StoreCollectionTrait;
    use CollectionTrait;

    public const MULTI_STORE_FIELDS_MAP = [
        AuthorInterface::NAME => 'IFNULL(noDefaultStore.name, store.name)',
        AuthorInterface::META_TITLE => 'IFNULL(noDefaultStore.meta_title, store.meta_title)',
        AuthorInterface::META_DESCRIPTION => 'IFNULL(noDefaultStore.meta_description, store.meta_description)',
        AuthorInterface::META_TAGS => 'IFNULL(noDefaultStore.meta_tags, store.meta_tags)',
        AuthorInterface::META_ROBOTS => 'IFNULL(noDefaultStore.meta_robots, store.meta_robots)',
        AuthorInterface::DESCRIPTION => 'IFNULL(noDefaultStore.description, store.description)',
        AuthorInterface::SHORT_DESCRIPTION => 'IFNULL(noDefaultStore.short_description, store.short_description)',
        AuthorInterface::JOB_TITLE => 'IFNULL(noDefaultStore.job_title, store.job_title)',
        AuthorInterface::URL_KEY => 'IFNULL(noDefaultStore.url_key, store.url_key)',
    ];

    /**
     * @var string
     */
    protected $_idFieldName = 'author_id';

    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'author_id' => 'main_table.author_id'
        ]
    ];

    protected function _renderFiltersBefore(): void
    {
        $this->renderFilters();
        if ($this->getQueryText()) {
            $this->getSelect()->group('main_table.author_id');
        }
    }

    public function getStoreTable(): string
    {
        return $this->getTable(AuthorResource::STORE_TABLE_NAME);
    }

    public function _construct()
    {
        $this->_init(Author::class, AuthorResource::class);
    }
}
