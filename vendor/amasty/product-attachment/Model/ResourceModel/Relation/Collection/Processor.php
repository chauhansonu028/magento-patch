<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\ResourceModel\Relation\Collection;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Processor
{
    /**
     * @var AbstractCollection
     */
    private $collection;

    /**
     * @var string[]
     */
    private $relationTblAliases = [];

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param string $relationTblName
     * @param string $relationTblLinkField
     * @param string $mainTblColumnName
     * @param string $itemNewFieldName
     * @param array|string $columnNamesFromRelationTbl
     * @param bool $dataAsArray
     * @param int|null $storeId
     * @return void
     */
    public function attachRelationDataToItem(
        string $relationTblName,
        string $relationTblLinkField,
        string $mainTblColumnName,
        string $itemNewFieldName,
        $columnNamesFromRelationTbl,
        bool $dataAsArray = true,
        ?int $storeId = null
    ):void {
        $connection = $this->collection->getConnection();

        $ids = $this->collection->getColumnValues($mainTblColumnName);
        if (count($ids)) {
            $relationTblAlias = $relationTblName . '_tbl';
            $select = $connection->select()
                ->from([$relationTblAlias => $this->collection->getTable($relationTblName)])
                ->where($relationTblAlias . '.' . $relationTblLinkField . ' IN (?)', $ids);
            if (null !== $storeId) {
                $select->where($relationTblAlias . '.store_id = ?', $storeId);
            }
            $relationTblRows = $connection->fetchAll($select);

            foreach ($this->collection as $item) {
                $result = $this->prepareItemData(
                    $item,
                    $mainTblColumnName,
                    $relationTblLinkField,
                    $columnNamesFromRelationTbl,
                    $relationTblRows
                );
                if (strpos($itemNewFieldName, '/') !== false) {
                    $this->setByPath($item, $itemNewFieldName, $result);
                } else {
                    $result = $dataAsArray ? $result : array_shift($result);
                    $item->setData($itemNewFieldName, $result);
                }
            }
        }
    }

    /**
     * @param AbstractModel $item
     * @param string $path
     * @param mixed $value
     * @return void
     */
    private function setByPath($item, string $path, $value): void
    {
        $newData = $value;
        $explodedPath = explode('/', $path);
        $firstKey = array_shift($explodedPath);
        foreach (array_reverse($explodedPath) as $key) {
            $newData = [$key => $newData];
        }
        $item->setData($firstKey, array_merge_recursive($item->getData($firstKey) ?? [], $newData));
    }

    /**
     * @param AbstractModel $item
     * @param string $mainTblColumnName
     * @param string $relationTblLinkField
     * @param array|string $columnNamesFromRelationTbl
     * @param array $relationTblRows
     * @return array
     */
    private function prepareItemData(
        $item,
        string $mainTblColumnName,
        string $relationTblLinkField,
        string $columnNamesFromRelationTbl,
        array $relationTblRows
    ): array {
        $result = [];
        $mainTblColumnValue = $item->getData($mainTblColumnName);
        foreach ($relationTblRows as $row) {
            if ($row[$relationTblLinkField] == $mainTblColumnValue) {
                if (is_array($columnNamesFromRelationTbl)) {
                    $fieldValue = [];
                    foreach ($columnNamesFromRelationTbl as $columnNameRelationTbl) {
                        $fieldValue[$columnNameRelationTbl] = $row[$columnNameRelationTbl];
                    }
                    $result[] = $fieldValue;
                } else {
                    $result[] = $row[$columnNamesFromRelationTbl];
                }
            }
        }

        return $result;
    }

    /**
     * @param string $filterName
     * @param string $relationTblFilterFieldName
     * @param string $relationTblName
     * @param string $relationTblLinkField
     * @param string $mainTblColumnName
     * @param int|null $storeId
     * @param string $mainTblAlias
     * @return void
     */
    public function joinRelationTableByFilter(
        string $filterName,
        string $relationTblFilterFieldName,
        string $relationTblName,
        string $relationTblLinkField,
        string $mainTblColumnName,
        ?int $storeId = null,
        string $mainTblAlias = 'main_table'
    ):void {
        $relationTblAlias = $filterName . '_tbl';
        if ($this->collection->getFilter($filterName)) {
            if (!in_array($relationTblAlias, $this->relationTblAliases)) {
                $this->relationTblAliases[] = $relationTblAlias;
                $storeJoinCondition = '';
                if (null !== $storeId) {
                    $storeJoinCondition = ' AND ' . $mainTblAlias . '.' . '.store_id = '
                        . $relationTblAlias . '.' . $storeId;
                }
                $this->collection->getSelect()
                    ->joinLeft(
                        [$relationTblAlias => $this->collection->getTable($relationTblName)],
                        $mainTblAlias . '.' . $mainTblColumnName
                        . ' = ' . $relationTblAlias . '.' . $relationTblLinkField . $storeJoinCondition,
                        []
                    );
            }

            $this->collection->addFilterToMap($filterName, $relationTblAlias . '.' . $relationTblFilterFieldName);
        }
    }
}
