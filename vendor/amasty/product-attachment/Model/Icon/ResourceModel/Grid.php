<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Icon\ResourceModel;

use Amasty\ProductAttachment\Api\Data\IconInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Grid extends SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = Icon::TABLE_NAME,
        $resourceModel = Icon::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->addFieldToSelect(
            new \Zend_Db_Expr(
                '`main_table`.*, ('
                    . 'select group_concat(' . IconInterface::EXTENSION . ' separator \', \')'
                    . ' from ' . $this->getTable(Icon::ICON_EXTENSION_TABLE_NAME)
                    . ' where ' . IconInterface::ICON_ID . ' = `main_table`.' . IconInterface::ICON_ID
                .') as extension'
            )
        );
    }
}
