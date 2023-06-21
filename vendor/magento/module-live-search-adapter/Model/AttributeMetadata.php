<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class for Attribute Metadata
 */
class AttributeMetadata
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var SearchScopeResolver
     */
    private SearchScopeResolver $scopeResolver;

    /**
     * @var AttributeMetadataCache
     */
    private AttributeMetadataCache $attributeMetadataCache;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SearchScopeResolver $scopeResolver
     * @param AttributeMetadataCache $attributeMetadataCache
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SearchScopeResolver $scopeResolver,
        AttributeMetadataCache $attributeMetadataCache
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeResolver = $scopeResolver;
        $this->attributeMetadataCache = $attributeMetadataCache;
    }

    /**
     * Get select for attributes.
     *
     * @param array $attributeCodes
     * @return Select
     */
    private function getAttributeOptionsSelect(array $attributeCodes): Select
    {
        $connection = $this->resourceConnection->getConnection();
        return $connection->select()
            ->from(
                ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                ['attributeCode' => 'a.attribute_code', 'sourceModel' => 'a.source_model']
            )
            ->joinLeft(
                ['o' => $this->resourceConnection->getTableName('eav_attribute_option')],
                'o.attribute_id = a.attribute_id',
                [
                    'optionId' => 'o.option_id'
                ]
            )
            ->joinLeft(
                ['v' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                'o.option_id = v.option_id',
                ['optionValue' => 'v.value']
            )
            ->joinLeft(
                ['s' => $this->resourceConnection->getTableName('store')],
                'v.store_id = s.store_id',
                ['storeViewCode' => 's.code']
            )
            ->where('a.attribute_code IN (?)', $attributeCodes);
    }

    /**
     * Returns attributes metadata by given attributes codes.
     *
     * @param array $attributeCodes
     * @return array
     */
    public function getAttributesMetadata(array $attributeCodes): array
    {
        $attributeMetadata = [];
        $attributeCodesNotInCache = [];
        foreach ($attributeCodes as $attributeCode) {
            $attributeOptions = $this->attributeMetadataCache->load($attributeCode);
            if (!$attributeOptions) {
                $attributeCodesNotInCache[] = $attributeCode;
            } else {
                $attributeMetadata[$attributeCode]['options'] = $attributeOptions;
            }
        }

        if (!empty($attributeCodesNotInCache)) {
            $connection = $this->resourceConnection->getConnection();
            $select = $this->getAttributeOptionsSelect($attributeCodesNotInCache);
            $rows = $connection->fetchAll($select);

            foreach ($rows as $row) {
                if ($row['sourceModel'] == Boolean::class) {
                    $attributeMetadata[$row['attributeCode']]['options']['admin'][0] = 'no';
                    $attributeMetadata[$row['attributeCode']]['options']['admin'][1] = 'yes';
                } elseif ($row['optionId'] !== null) {
                    $attributeMetadata[$row['attributeCode']]['options'][$row['storeViewCode']][$row['optionId']] =
                        $row['optionValue'];
                }

                if (isset($attributeMetadata[$row['attributeCode']]['options'])) {
                    $this->attributeMetadataCache->save(
                        $row['attributeCode'],
                        $attributeMetadata[$row['attributeCode']]['options']
                    );
                }
            }
        }

        return $attributeMetadata;
    }

    /**
     * Returns option by id.
     *
     * @param string $attributeCode
     * @param int $optionId
     * @return string|null
     */
    public function getOptionById(string $attributeCode, int $optionId): ?string
    {
        $storeViewCode = $this->scopeResolver->getStoreViewCode();
        $attributeMetadata = $this->getAttributesMetadata([$attributeCode]);
        if (isset($attributeMetadata[$attributeCode]['options'])) {
            $options = $attributeMetadata[$attributeCode]['options'];
            if (isset($options[$storeViewCode][$optionId])) {
                return $options[$storeViewCode][$optionId];
            }
            if (isset($options['admin'][$optionId])) {
                return $options['admin'][$optionId];
            }
        }

        return null;
    }
}
