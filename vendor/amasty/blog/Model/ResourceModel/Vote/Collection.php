<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Vote;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\Data\VoteInterface;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\ResourceModel\Vote as ResourceVote;
use Amasty\Blog\Model\Source\PostStatus;
use Amasty\Blog\Model\Vote;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public const POST_TABLE_ALIAS = 'post';

    public const POSTS_STORE_TABLE_ALIAS = 'posts_store';

    public function _construct()
    {
        $this->_init(
            Vote::class,
            ResourceVote::class
        );
    }

    public function addIpFilter(string $ip): self
    {
        return $this->addFieldToFilter(VoteInterface::IP, $ip);
    }

    public function addStoreFilter(array $storeIds): self
    {
        return $this
            ->joinPostsStoreTable([PostInterface::STORE_ID])
            ->addFieldToFilter(PostInterface::STORE_ID, $storeIds);
    }

    public function sortByPost(string $direction = self::SORT_ORDER_DESC): self
    {
        return $this
            ->joinPostTable([PostInterface::PUBLISHED_AT])
            ->setOrder(PostInterface::PUBLISHED_AT, $direction);
    }

    public function joinPostTable(array $cols = ['*']): self
    {
        if (!isset($this->_joinedTables[self::POST_TABLE_ALIAS])) {
            $this->getSelect()->join(
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

    public function joinPostsStoreTable(array $cols = ['*']): self
    {
        if (!isset($this->_joinedTables[Posts::POSTS_STORE_TABLE])) {
            $this->getSelect()->join(
                [self::POSTS_STORE_TABLE_ALIAS => $this->getTable(Posts::POSTS_STORE_TABLE)],
                sprintf(
                    'main_table.%s = %s.%s AND %s.%s = %s',
                    PostInterface::POST_ID,
                    self::POSTS_STORE_TABLE_ALIAS,
                    PostInterface::POST_ID,
                    self::POSTS_STORE_TABLE_ALIAS,
                    PostInterface::STATUS,
                    PostStatus::STATUS_ENABLED
                ),
                $cols
            );
            $this->_joinedTables[Posts::POSTS_STORE_TABLE] = true;
        }

        return $this;
    }
}
