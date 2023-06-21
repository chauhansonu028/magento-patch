<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ResourceModel\Posts;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Expr;

class NavigationPosition extends AbstractDb
{
    public function _construct()
    {
        $this->_init(Posts::TABLE_NAME, PostInterface::POST_ID);
    }

    /**
     * @param array $stores
     * @return array
     */
    public function getPositions(array $stores): array
    {
        $joinCondition = $this->getConnection()->quoteInto(
            sprintf(
                'post_store.%s = post.%s %s post_store.%s IN (?)',
                PostInterface::POST_ID,
                PostInterface::POST_ID,
                Select::SQL_AND,
                PostInterface::STORE_ID
            ),
            $stores
        );
        $whereCondition = sprintf(
            'post_store.%s = ? %s post_store.%s <= NOW()',
            PostInterface::STATUS,
            Select::SQL_AND,
            PostInterface::PUBLISHED_AT
        );
        $orderCondition = new Zend_Db_Expr(
            sprintf(
                'post_store.%s %s, post_store.%s %s',
                PostInterface::PUBLISHED_AT,
                Select::SQL_DESC,
                PostInterface::POST_ID,
                Select::SQL_DESC
            )
        );
        $select = $this->getConnection()->select()->from(
            ['post' => $this->getTable(Posts::TABLE_NAME)],
            [sprintf('post.%s', PostInterface::POST_ID)]
        )->joinInner(
            ['post_store' => $this->getTable(Posts::POSTS_STORE_TABLE)],
            $joinCondition,
            []
        )->where(
            $whereCondition,
            PostStatus::STATUS_ENABLED
        )->group(sprintf('post_store.%s', PostInterface::POST_ID))->order($orderCondition);

        return $this->getConnection()->fetchCol($select);
    }
}
