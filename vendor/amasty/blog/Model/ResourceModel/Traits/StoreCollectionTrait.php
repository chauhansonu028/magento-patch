<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Traits;

use InvalidArgumentException;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\Store;
use Zend_Db_Expr;

trait StoreCollectionTrait
{
    public function addDefaultStore(bool $isAddColumns = true): self
    {
        $table = $this->getMainTable() . '_store';
        $idFieldName = $this->getResource()->getIdFieldName();
        $this->getSelect()->joinLeft(
            ['store' => $table],
            sprintf(
                'store.%s = main_table.%s AND store.store_id = %s',
                $idFieldName,
                $idFieldName,
                Store::DEFAULT_STORE_ID
            )
        )->group('main_table.' . $idFieldName);
        if ($isAddColumns) {
            $this->getSelect()->columns('store.*');
        }
        if (method_exists($this, 'setIsStoreDataAdded')) {
            $this->setIsStoreDataAdded(true);
        }

        return $this;
    }

    public function addStore(?int $storeId, bool $isAddColumns = true): self
    {
        $table = $this->getMainTable() . '_store';
        $idFieldName = $this->getResource()->getIdFieldName();

        $this->getSelect()->joinLeft(
            ['noDefaultStore' => $table],
            sprintf(
                'noDefaultStore.%s = main_table.%s AND noDefaultStore.store_id = %s',
                $idFieldName,
                $idFieldName,
                $storeId
            ),
            []
        );
        if ($isAddColumns) {
            $this->getSelect()->columns('noDefaultStore.*');
        }

        return $this;
    }

    public function addStoreWithDefault(?int $storeId): self
    {
        $this->addDefaultStore(false)->addStore($storeId, false);
        $idFieldName = $this->getResource()->getIdFieldName();
        foreach (self::MULTI_STORE_FIELDS_MAP as $key => $field) {
            $this->getSelect()->columns([$key => $field]);
        }
        $this->getSelect()->group('main_table.' . $idFieldName);

        return $this;
    }

    public function setLimit(?int $limit): void
    {
        $this->getSelect()->limit($limit);
    }

    public function getStoreColumn(string $column): string
    {
        return self::MULTI_STORE_FIELDS_MAP[$column] ?? $column;
    }

    public function addStoreFieldToFilter($field, $condition = null): void
    {
        if (!isset(self::MULTI_STORE_FIELDS_MAP[$field])) {
            throw new InvalidArgumentException(__("Store configured field `{$field}` doesn't exists")->render());
        }

        $field = new Zend_Db_Expr(self::MULTI_STORE_FIELDS_MAP[$field]);
        $this->getSelect()->where($this->getConnection()->prepareSqlCondition($field, $condition));
    }
}
