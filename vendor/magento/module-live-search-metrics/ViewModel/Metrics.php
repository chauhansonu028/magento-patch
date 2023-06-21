<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchMetrics\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\LiveSearchMetrics\Model\MetricsModel;

class Metrics implements ArgumentInterface
{
    /**
     * @var MetricsModel
     */
    private MetricsModel $metricsModel;

    /**
     * @param MetricsModel $metricsModel
     */
    public function __construct(
        MetricsModel $metricsModel
    ) {
        $this->metricsModel = $metricsModel;
    }

    /**
     * Returns search request.
     *
     * @return string|null
     */
    public function getSearchRequest(): ?string
    {
        return $this->metricsModel->getRequest();
    }

    /**
     * Returns search response.
     *
     * @return string|null
     */
    public function getSearchResponse(): ?string
    {
        return $this->metricsModel->getResponse();
    }
}
