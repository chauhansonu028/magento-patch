<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel;

use Amasty\Blog\Api\Data\CommentInterface;
use Magento\Framework\DB\Select;

class Comments extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'amasty_blog_comments';
    const TABLE_ALIAS = 'comments';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'comment_id');
    }

    public function getReplyIdsByCommentId(int $commentId, ?int $status = null): array
    {
        $select = $this->getConnection()->select()
            ->from(
                [self::TABLE_ALIAS => $this->getTable(self::TABLE_NAME)],
                [CommentInterface::COMMENT_ID]
            )
            ->where(sprintf('%s.%s IS NOT NULL', self::TABLE_ALIAS, CommentInterface::EMAIL))
            ->where(sprintf('%s.%s = %d', self::TABLE_ALIAS, CommentInterface::REPLY_TO, $commentId));

        $this->filterByStatus($select, $status);

        return $this->getConnection()->fetchCol($select);
    }

    public function getCommentsByPostId(int $postId, ?int $status = null): array
    {
        $select = $this->getConnection()->select()
            ->from(
                [self::TABLE_ALIAS => $this->getTable(self::TABLE_NAME)],
                [CommentInterface::COMMENT_ID, CommentInterface::REPLY_TO]
            )
            ->where(sprintf('%s.%s = %d', self::TABLE_ALIAS, CommentInterface::POST_ID, $postId));

        $this->filterByStatus($select, $status);

        return $this->getConnection()->fetchPairs($select);
    }

    public function getEmailsByCommentId(array $ids): array
    {
        $select = $this->getConnection()->select()
            ->from(
                [self::TABLE_ALIAS => $this->getTable(self::TABLE_NAME)],
                [CommentInterface::EMAIL]
            )
            ->where(sprintf('%s.%s IS NOT NULL', self::TABLE_ALIAS, CommentInterface::EMAIL))
            ->where(sprintf('%s.%s IN (?)', self::TABLE_ALIAS, CommentInterface::COMMENT_ID), $ids);

        return $this->getConnection()->fetchCol($select);
    }

    private function filterByStatus(Select $select, ?int $status = null)
    {
        if ($status !== null) {
            $select->where(sprintf('%s.%s = %d', self::TABLE_ALIAS, CommentInterface::STATUS, $status));
        }
    }
}
