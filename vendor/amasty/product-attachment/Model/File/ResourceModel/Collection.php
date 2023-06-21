<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\ResourceModel;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Model\ResourceModel\Relation\Collection\Processor;
use Amasty\ProductAttachment\Model\ResourceModel\Relation\Collection\ProcessorFactory;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var Processor
     */
    private $relationProcessor;

    /**
     * @var array
     */
    private $attachRelation;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ProcessorFactory $relationProcessorFactory,
        array $attachRelation = [],
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->relationProcessor = $relationProcessorFactory->create(['collection' => $this]);
        $this->attachRelation = $attachRelation;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\ProductAttachment\Model\File\File::class,
            \Amasty\ProductAttachment\Model\File\ResourceModel\File::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->group('main_table.file_id');

        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        foreach ($this->attachRelation as $attach) {
            if ($field == $attach['itemNewFieldName']) {
                $this->addFilter($field, ['in' => $condition], 'public');

                return $this;
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }

    protected function _afterLoad()
    {
        foreach ($this->attachRelation as $attach) {
            $this->relationProcessor->attachRelationDataToItem(
                $attach['relationTblName'],
                $attach['relationTblLinkField'],
                $attach['mainTblColumnName'],
                $attach['itemNewFieldName'],
                $attach['columnNamesFromRelationTbl']
            );
        }

        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        foreach ($this->attachRelation as $attach) {
            $this->relationProcessor->joinRelationTableByFilter(
                $attach['itemNewFieldName'],
                $attach['columnNamesFromRelationTbl'],
                $attach['relationTblName'],
                $attach['relationTblLinkField'],
                $attach['mainTblColumnName']
            );
        }

        parent::_renderFiltersBefore();
    }

    /**
     * @return $this
     */
    public function addFileData($storeId = 0)
    {
        $this->addFilterToMap('file_id', 'main_table.file_id');
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
            File::FILE_STORE_TABLE_NAME . '.' . FileScopeInterface::STORE_ID . ' = ' . $storeId
        );

        return $this;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function addInsertListingFileData($storeId = 0)
    {
        if (!$storeId) {
            $this->addFileData();

            return $this;
        }

        $this->addFieldToSelect(FileInterface::FILE_ID);
        $this->addFieldToSelect(FileInterface::ATTACHMENT_TYPE);
        $this->addFieldToSelect(FileInterface::SIZE);
        $this->addFieldToSelect(FileInterface::MIME_TYPE);
        $this->addFieldToSelect(FileInterface::EXTENSION);
        $this->addFilterToMap('file_id', 'main_table.file_id');

        $fields = [
            FileScopeInterface::LABEL,
            FileScopeInterface::FILENAME,
            FileScopeInterface::INCLUDE_IN_ORDER,
            FileScopeInterface::IS_VISIBLE
        ];

        foreach ($fields as $field) {
            $field2 = File::FILE_STORE_TABLE_NAME . '_store.' . $field;
            $field1 = File::FILE_STORE_TABLE_NAME . '.' . $field;
            $this->addFieldToSelect(new \Zend_Db_Expr('IFNULL(' . $field2 . ', ' . $field1 . ')'), $field);
        }

        $alias = File::FILE_STORE_TABLE_NAME . '_store';
        $this->getSelect()->joinLeft(
            [$alias => $this->getTable(File::FILE_STORE_TABLE_NAME)],
            '(main_table.' . FileInterface::FILE_ID
            . ' = ' . File::FILE_STORE_TABLE_NAME . '_store'
            . '.' . FileScopeInterface::FILE_ID
            . ' AND ' . File::FILE_STORE_TABLE_NAME . '_store' . '.' . FileScopeInterface::STORE_ID
            . '=' . (int)$storeId . ')',
            []
        );

        $this->join(
            File::FILE_STORE_TABLE_NAME,
            'main_table.' . FileInterface::FILE_ID .
            ' = ' . File::FILE_STORE_TABLE_NAME . '.' . FileScopeInterface::FILE_ID,
            []
        );
        $this->addFieldToFilter(
            File::FILE_STORE_TABLE_NAME . '.' . FileScopeInterface::STORE_ID,
            0
        );

        return $this;
    }

    public function addIdFilter($fileId, $exclude = false)
    {
        if (empty($fileId)) {
            $this->_setIsLoaded(true);
            return $this;
        }
        if (is_array($fileId)) {
            if (!empty($fileId)) {
                if ($exclude) {
                    $condition = ['nin' => $fileId];
                } else {
                    $condition = ['in' => $fileId];
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = ['neq' => $fileId];
            } else {
                $condition = $fileId;
            }
        }
        $this->addFieldToFilter('file_id', $condition);
        return $this;
    }

    /**
     * @return $this
     */
    public function addLinkFilter()
    {
        $this->addFieldToFilter(FileInterface::ATTACHMENT_TYPE, AttachmentType::LINK);

        return $this;
    }
}
