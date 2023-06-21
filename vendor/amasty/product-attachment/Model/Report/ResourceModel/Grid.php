<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Report\ResourceModel;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Grid extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'store_id' => 'main_table.store_id',
            'file_id' => 'main_table.file_id'
        ]
    ];

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = File::REPORT_TABLE_NAME,
        $resourceModel = \Amasty\ProductAttachment\Model\Report\ResourceModel\Item::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->join(
            File::FILE_STORE_TABLE_NAME,
            'main_table.' . FileInterface::FILE_ID .
            ' = ' . File::FILE_STORE_TABLE_NAME . '.' . FileScopeInterface::FILE_ID,
            [
                FileScopeInterface::LABEL,
                FileScopeInterface::FILENAME,
                FileScopeInterface::INCLUDE_IN_ORDER,
                FileScopeInterface::IS_VISIBLE,
            ]
        );
        $this->getSelect()->where(
            File::FILE_STORE_TABLE_NAME . '.' . FileScopeInterface::STORE_ID . ' = 0'
        );
    }
}
