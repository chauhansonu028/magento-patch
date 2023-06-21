<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\FilterHandler;

interface FilterHandlerInterface
{
    /**
     * Get filter key
     *
     * @return string
     */
    public function getFilterKey(): string;

    /**
     * Get filter variables
     *
     * @return array
     */
    public function getFilterVariables(): array;

    /**
     * Get filter type
     *
     * @return string
     */
    public function getFilterType(): string;
}
