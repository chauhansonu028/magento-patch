<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Setup\Patch\Schema;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class FileCreateTriggers implements SchemaPatchInterface
{
    /**
     * @var TriggerFactory
     */
    private $triggerFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        TriggerFactory $triggerFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->triggerFactory = $triggerFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function apply()
    {
        $this->createUpdateTrigger(
            File::FILE_STORE_TABLE_NAME
        );

        $this->createUpdateTrigger(
            File::FILE_STORE_CATEGORY_TABLE_NAME
        );

        $this->createUpdateTrigger(
            File::FILE_STORE_PRODUCT_TABLE_NAME
        );

        $this->createUpdateTrigger(
            File::FILE_STORE_CATEGORY_PRODUCT_TABLE_NAME
        );

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    private function createUpdateTrigger($tableName)
    {
        /** @var Trigger $trigger */
        $trigger = $this->triggerFactory->create()
            ->setName('updated_time_for_' . $tableName)
            ->setTime(Trigger::TIME_AFTER)
            ->setEvent(Trigger::EVENT_UPDATE)
            ->setTable($this->resourceConnection->getTableName($tableName));
        $trigger->addStatement($this->getUpdatedRowsStatement());
        $this->resourceConnection->getConnection()->dropTrigger($trigger->getName());
        $this->resourceConnection->getConnection()->createTrigger($trigger);
    }

    private function getUpdatedRowsStatement()
    {
        return sprintf(
            "UPDATE %s SET %s = CURRENT_TIMESTAMP() WHERE %s = NEW.%s",
            $this->resourceConnection->getTableName(File::TABLE_NAME),
            FileInterface::UPDATED_AT,
            FileInterface::FILE_ID,
            FileScopeInterface::FILE_ID
        );
    }
}
