<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\Aggregation;

use Magento\Framework\Search\Response\Bucket;

interface BucketHandlerInterface
{
    /**
     * Returns name of the bucket.
     *
     * @return string
     */
    public function getBucketName(): string;

    /**
     * Returns bucket to handle.
     *
     * @return Bucket|null
     */
    public function getBucket(): ?Bucket;
}
