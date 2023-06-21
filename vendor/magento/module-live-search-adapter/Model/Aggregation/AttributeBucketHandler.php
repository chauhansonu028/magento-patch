<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\Aggregation;

use Magento\Framework\Search\Response\Aggregation\ValueFactory;
use Magento\Framework\Search\Response\Bucket;
use Magento\Framework\Search\Response\BucketFactory;

/**
 * Bucket handler for product attributes.
 */
class AttributeBucketHandler implements BucketHandlerInterface
{
    /**
     * @var string
     */
    private string $storeViewCode;

    /**
     * @var string
     */
    private string $attributeCode;

    /**
     * @var array
     */
    private array $rawBuckets;

    /**
     * @var array
     */
    private array $attributesMetadata;

    /**
     * @var BucketFactory
     */
    private BucketFactory $bucketFactory;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @param string $storeViewCode
     * @param string $attributeCode
     * @param array $rawBuckets
     * @param array $attributesMetadata
     * @param BucketFactory $bucketFactory
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        string $storeViewCode,
        string $attributeCode,
        array $rawBuckets,
        array $attributesMetadata,
        BucketFactory $bucketFactory,
        ValueFactory $valueFactory
    ) {
        $this->storeViewCode = $storeViewCode;
        $this->attributeCode = $attributeCode;
        $this->rawBuckets = $rawBuckets;
        $this->attributesMetadata = $attributesMetadata;
        $this->bucketFactory = $bucketFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getBucketName(): string
    {
        return $this->attributeCode . '_bucket';
    }

    /**
     * @inheritdoc
     *
     * @return Bucket|null
     */
    public function getBucket(): ?Bucket
    {
        $bucketValues = [];
        if (isset($this->attributesMetadata[$this->attributeCode]['options'])) {
            $attributeOptions = $this->attributesMetadata[$this->attributeCode]['options'];
            foreach ($this->rawBuckets as $bucket) {
                $optionId = null;
                if (isset($attributeOptions[$this->storeViewCode])) {
                    $optionId = array_search($bucket['title'], $attributeOptions[$this->storeViewCode]);
                }
                if (empty($optionId) && isset($bucket['title'], $attributeOptions['admin'])) {
                    $optionId = array_search($bucket['title'], $attributeOptions['admin']);
                }
                if ($optionId) {
                    $metrics = [
                        'count' => $bucket['count'],
                        'value' => $optionId
                    ];
                    $bucketValues[] = $this->valueFactory->create(
                        [
                        'value' => $optionId,
                        'metrics' => $metrics
                        ]
                    );
                }
            }
        }

        if (!empty($bucketValues)) {
            return $this->bucketFactory->create(
                [
                'name' => $this->getBucketName(),
                'values' => $bucketValues
                ]
            );
        }

        return null;
    }
}
