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
 * Attributes handler for empty buckets.
 */
class AttributeEmptyBucketHandler implements BucketHandlerInterface
{
    /**
     * @var string
     */
    private string $attributeCode;

    /**
     * @var BucketFactory
     */
    private BucketFactory $bucketFactory;

    /**
     * @param string $attributeCode
     * @param BucketFactory $bucketFactory
     */
    public function __construct(
        string $attributeCode,
        BucketFactory $bucketFactory
    ) {
        $this->attributeCode = $attributeCode;
        $this->bucketFactory = $bucketFactory;
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
        return $this->bucketFactory->create(
            [
            'name' => $this->getBucketName(),
            'values' => []
            ]
        );
    }
}
