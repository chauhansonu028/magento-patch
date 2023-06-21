<?php
/**
 * Provides countries options.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Source;

use Digitalriver\DrPay\Model\ResourceModel\CountryOfOrigin\CollectionFactory as CountryOfOriginCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\Collection;

/**
 * Class CountryOfOrigin Source Provider
 */
abstract class DrPayAbstractSource extends AbstractSource
{
    /**
     * Used for sorting in Product Grid
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir
     * @return AbstractSource
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addValueSortToCollection($collection, $dir = Collection::SORT_ORDER_DESC) : AbstractSource
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeId = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();
        $linkField = $this->getAttribute()->getEntity()->getLinkField();

        if ($this->getAttribute()->isScopeGlobal()) {
            $tableName = $attributeCode . '_t';

            $collection->getSelect()->joinLeft(
                [$tableName => $attributeTable],
                "e.{$linkField}={$tableName}.{$linkField}" .
                " AND {$tableName}.attribute_id='{$attributeId}'" .
                " AND {$tableName}.store_id='0'",
                []
            );

            $valueExpr = $tableName . '.value';
            $collection->getSelect()->order(
                [
                    "ISNULL($valueExpr)",
                    $valueExpr . ' ' . $dir
                ]
            );
        }
        return parent::addValueSortToCollection($collection, $dir);
    }
}
