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
 * Handler for price bucket aggregation.
 */
class PriceBucketHandler implements BucketHandlerInterface
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
        return 'price_bucket';
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
            $metrics = [
                'count' => $bucket['count'],
                'value' => $bucket['from'] . '_' . $bucket['to']
            ];
            $bucketValues[] = $this->valueFactory->create(
                [
                'value' => $metrics['value'],
                'metrics' => $metrics
                ]
            );
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
