<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\ResourceModel\Abstracts;

use Amasty\Blog\Model\ResourceModel\Traits\CollectionTrait;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    use CollectionTrait;

    /**
     * @var bool
     */
    private $isStoreDataAdded = false;

    /**
     * @var array
     */
    private $storeIds;

    /**
     * @var string
     */
    private $queryText;

    /**
     * @throws LocalizedException
     */
    protected function _beforeLoad(): AbstractCollection
    {
        $this->applyStoreFilter();

        return parent::_beforeLoad();
    }

    /**
     * @param string $field
     * @param string $direction
     * @throws LocalizedException
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == "stores") {
            $this->addStoreData()->getSelect()->order('store.store_id ' . $direction);
        } else {
            return parent::setOrder($field, $direction);
        }

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    private function addStoreData(): self
    {
        if ($this->isStoreDataAdded()) {
            return $this;
        }

        $this->setIsStoreDataAdded(true);
        $table = $this->getMainTable() . "_store";
        $idFieldName = $this->getResource()->getIdFieldName();

        $this->getSelect()
            ->joinInner(['store' => $table], 'store.' . $idFieldName . ' = main_table.' . $idFieldName, [])
            ->columns('store.*')
            ->group('main_table.' . $idFieldName);

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function applyStoreFilter()
    {
        if ($this->storeIds) {
            $this->addStoreData();
            $store = $this->storeIds;

            if (!is_array($store)) {
                $store = [$store];
            }

            $storesFilter = "'" . implode("','", $store) . "'";
            $this->getSelect()->where('store.store_id IN (' . $storesFilter . ')');
        }

        return $this;
    }

    /**
     * @param $store
     *
     * @return $this
     */
    public function addStoreFilter($store)
    {
        if (is_array($store)) {
            array_push($store, Store::DEFAULT_STORE_ID);
            $this->storeIds = $store;
        } else {
            $this->storeIds = [$store, Store::DEFAULT_STORE_ID];
        }

        return $this;
    }

    public function getQueryText(): ?string
    {
        return $this->queryText;
    }

    public function setQueryText(string $queryText): self
    {
        $this->queryText = $queryText;

        return $this;
    }

    public function isStoreDataAdded(): bool
    {
        return $this->isStoreDataAdded;
    }

    public function setIsStoreDataAdded(bool $isStoreDataAdded): self
    {
        $this->isStoreDataAdded = $isStoreDataAdded;

        return $this;
    }

    public function getStoreTable(): ?string
    {
        return null;
    }

    public function getStoreColumn(string $column): string
    {
        return $column;
    }
}
