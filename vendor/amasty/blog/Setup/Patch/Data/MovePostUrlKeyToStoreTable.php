<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore\ExtractUrlKeysFromPostEntity;

class MovePostUrlKeyToStoreTable extends MoveUrlKeyToStoreTable
{
    public const ENTITIES_TABLES = [
        PostInterface::POST_ID => PostInterface::POSTS_STORE_TABLE
    ];

    protected function getTempTableName(): string
    {
        return ExtractUrlKeysFromPostEntity::TEMPORARY_TABLE_NAME;
    }
}
