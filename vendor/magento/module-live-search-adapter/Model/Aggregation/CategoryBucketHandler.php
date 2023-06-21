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
 * Bucket handler for category attribute.
 */
class CategoryBucketHandler implements BucketHandlerInterface
{
    /**
     * @var array
     */
    private array $rawBuckets;

    /**
     * @var BucketFactory
     */
    private BucketFactory $bucketFactory;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @param array $rawBuckets
     * @param BucketFactory $bucketFactory
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        array $rawBuckets,
        BucketFactory $bucketFactory,
        ValueFactory $valueFactory
    ) {
        $this->rawBuckets = $rawBuckets;
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
        return 'category_bucket';
    }

    /**
     * @inheritdoc
     *
     * @return Bucket|null
     */
    public function getBucket(): ?Bucket
    {
        $bucketValues = [];
        foreach ($this->rawBuckets as $bucket) {
            $bucketId = $bucket['id'];
            if ($bucketId) {
                $metrics = [
                    'count' => $bucket['count'],
                    'value' => (int)$bucketId
                ];
                $bucketValues[] = $this->valueFactory->create(
                    [
                    'value' => (int)$bucketId,
                    'metrics' => $metrics
                    ]
                );
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
