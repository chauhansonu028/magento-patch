<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class ApplyBlogLayoutConfig implements DataPatchInterface, PatchRevertableInterface
{
    const CONFIGS_FOR_APPLY = [
        'amblog/layout/mobile_list',
        'amblog/layout/mobile_post',
        'amblog/layout/desktop_list',
        'amblog/layout/desktop_post'
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): ApplyBlogLayoutConfig
    {
        return $this;
    }

    public function revert()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select();
        $tableName = $this->moduleDataSetup->getTable('layout_update');
        $select->from($tableName);
        $condition = $connection->prepareSqlCondition(
            'handle',
            [
                'in' => [
                    'amasty_blog_mobile_post',
                    'amasty_blog_desktop_list',
                    'amasty_blog_desktop_post',
                    'amasty_blog_mobile_list'
                ]
            ]
        );
        $select->where($condition);
        $connection->query($connection->deleteFromSelect($select, $tableName));
    }
}
