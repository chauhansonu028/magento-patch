<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Comments;

use Amasty\Blog\Api\Data\CommentInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\ResourceModel\Comments;
use Amasty\Blog\Model\Source\CommentStatus;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\DB\Select;

/**
 * Class
 */
class Collection extends \Amasty\Blog\Model\ResourceModel\Abstracts\Collection
{
    public const POST_TABLE_ALIAS = 'post';

    /**
     * @var string
     */
    protected $_idFieldName = 'comment_id';

    /**
     * @var string
     */
    protected $storeIds = '';

    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'comment_id' => 'main_table.comment_id'
        ]
    ];

    public function _construct()
    {
        $this->_init(\Amasty\Blog\Model\Comments::class, Comments::class);
    }

    public function sortByCreatedAt(string $direction = self::SORT_ORDER_DESC): self
    {
        return $this->setOrder('main_table.' . CommentInterface::CREATED_AT, $direction);
    }

    /**
     * @return Select
     */
    public function getSelectCountSql(): Select
    {
        $this->applyStoreFilter();
        return parent::getSelectCountSql();
    }

    public function joinPostTable(array $cols = ['*']): self
    {
        if (!isset($this->_joinedTables[self::POST_TABLE_ALIAS])) {
            $this->getSelect()->joinLeft(
                [self::POST_TABLE_ALIAS => $this->getTable(Posts::PERSISTENT_NAME)],
                sprintf(
                    'main_table.%s = %s.%s',
                    PostInterface::POST_ID,
                    self::POST_TABLE_ALIAS,
                    PostInterface::POST_ID
                ),
                $cols
            );
            $this->_joinedTables[self::POST_TABLE_ALIAS] = true;
        }

        return $this;
    }

    /**
     * @param null $ownerSessionId
     * @return $this
     */
    public function addActiveFilter($ownerSessionId = null)
    {
        if ($ownerSessionId) {
            $activeStatus = CommentStatus::STATUS_APPROVED;
            $pendingStatus = CommentStatus::STATUS_PENDING;
            $this->getSelect()
                ->where(
                    new \Zend_Db_Expr(
                        '(main_table.status = "' . $activeStatus . '") OR
                        ((main_table.status = "' . $pendingStatus . '") AND
                        (main_table.session_id = "' . $ownerSessionId . '"))'
                    )
                );
        } else {
            $this->addFieldToFilter('main_table.status', CommentStatus::STATUS_APPROVED);
        }

        return $this;
    }

    public function addAllowedPostFilter(): self
    {
        $this->joinPostTable();
        $this->addFieldToFilter(
            sprintf('%s.%s', self::POST_TABLE_ALIAS, PostInterface::STATUS),
            PostStatus::STATUS_ENABLED
        );

        return $this;
    }

    public function addCustomerIdFilter(int $id): self
    {
        $this->addFieldToFilter(CommentInterface::CUSTOMER_ID, $id);

        return $this;
    }

    /**
     * @param $postId
     * @return $this
     */
    public function addPostFilter($postId)
    {
        $this->addFieldToFilter('post_id', $postId);

        return $this;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDateOrder($dir = 'DESC')
    {
        $this->getSelect()->order('main_table.created_at ' . $dir);

        return $this;
    }

    /**
     * @return $this
     */
    public function setNotReplies()
    {
        $this->getSelect()->where('main_table.reply_to IS NULL');

        return $this;
    }

    /**
     * @param $commentId
     * @return $this
     */
    public function setReplyToFilter($commentId)
    {
        $this->getSelect()
            ->where('main_table.reply_to = ?', $commentId);

        return $this;
    }

    /**
     * @param $storeIds
     * @return $this|\Amasty\Blog\Model\ResourceModel\Abstracts\Collection
     */
    public function addStoreFilter($storeIds)
    {
        $this->storeIds = $storeIds;

        return $this;
    }

    /**
     * @param null|array $storeIds
     *
     * @return $this
     */
    protected function applyStoreFilter($storeIds = null)
    {
        $storeIds = $storeIds ?: $this->storeIds;
        if ($storeIds) {
            if (!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }
            $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        }

        return $this;
    }
}
