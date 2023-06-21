<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\Aggregation;

/**
 * Factory for bucket handlers.
 */
class BucketHandlerFactory
{
    /**
     * @var CategoryBucketHandlerFactory
     */
    private CategoryBucketHandlerFactory $categoryBucketHandlerFactory;

    /**
     * @var PriceBucketHandlerFactory
     */
    private PriceBucketHandlerFactory $priceBucketHandlerFactory;

    /**
     * @var AttributeBucketHandlerFactory
     */
    private AttributeBucketHandlerFactory $attributeBucketHandlerFactory;

    /**
     * @param CategoryBucketHandlerFactory $categoryBucketHandlerFactory
     * @param PriceBucketHandlerFactory $priceBucketHandlerFactory
     * @param AttributeBucketHandlerFactory $attributeBucketHandlerFactory
     */
    public function __construct(
        CategoryBucketHandlerFactory $categoryBucketHandlerFactory,
        PriceBucketHandlerFactory $priceBucketHandlerFactory,
        AttributeBucketHandlerFactory $attributeBucketHandlerFactory
    ) {
        $this->categoryBucketHandlerFactory = $categoryBucketHandlerFactory;
        $this->priceBucketHandlerFactory = $priceBucketHandlerFactory;
        $this->attributeBucketHandlerFactory = $attributeBucketHandlerFactory;
    }

    /**
     * Resolver for bucker handlers.
     *
     * @param string $bucketName
     * @param array $rawBuckets
     * @param array $attributesMetadata
     * @param string $storeViewCode
     * @return BucketHandlerInterface
     */
    public function resolve(
        string $bucketName,
        array $rawBuckets,
        array $attributesMetadata,
        string $storeViewCode
    ): BucketHandlerInterface {
        return match ($bucketName) {
            'categories' => $this->categoryBucketHandlerFactory->create(['rawBuckets' => $rawBuckets]),
            'price' => $this->priceBucketHandlerFactory->create(['rawBuckets' => $rawBuckets]),
            default => $this->attributeBucketHandlerFactory->create(
                [
                    'storeViewCode' => $storeViewCode,
                    'attributeCode' => $bucketName,
                    'rawBuckets' => $rawBuckets,
                    'attributesMetadata' => $attributesMetadata
                ]
            ),
        };
    }
}
