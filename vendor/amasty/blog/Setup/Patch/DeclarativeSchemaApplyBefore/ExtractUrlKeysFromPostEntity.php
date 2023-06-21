<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ResourceModel\Posts;

class ExtractUrlKeysFromPostEntity extends ExtractUrlKeysFromBlogEntities
{
    public const ENTITIES_TABLES = [
        PostInterface::POST_ID => Posts::TABLE_NAME
    ];

    public const TEMPORARY_TABLE_NAME = 'amasty_blog_posts_url_keys';
}
