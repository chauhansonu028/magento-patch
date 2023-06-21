<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchMetrics\Block\Frontend;

use Exception;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\LiveSearch\Model\ModuleVersionReader;
use Magento\LiveSearchMetrics\Model\MetricsModel;
use Ramsey\Uuid\Uuid;
use Magento\Store\Model\ScopeInterface;
use Magento\LiveSearchAdapter\Model\AttributeMetadata;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\LiveSearch\Block\BaseSaaSContext;

/**
 * Template to display block with metrics.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class MetricsBlock extends Template
{
    /**
     * @var Toolbar
     */
    private Toolbar $toolbar;

    /**
     * @var AttributeMetadata
     */
    private AttributeMetadata $attributeMetadata;

    /**
     * @var MetricsModel
     */
    private MetricsModel $metricsModel;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var ModuleVersionReader
     */
    private ModuleVersionReader $moduleVersionReader;

    /**
     * @var BaseSaaSContext|null
     */
    private ?BaseSaaSContext $baseSaasContext;

    /**
     * @var string
     */
    private const METRICS_URL = "live_search_metrics/metrics_url";

    /**
     * @param Context $context
     * @param AttributeMetadata $attributeMetadata
     * @param Toolbar $toolbar
     * @param MetricsModel $metricsModel
     * @param Json $serializer
     * @param ModuleVersionReader $moduleVersionReader
     * @param BaseSaaSContext $baseSaaSContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        AttributeMetadata $attributeMetadata,
        Toolbar $toolbar,
        MetricsModel $metricsModel,
        Json $serializer,
        ModuleVersionReader $moduleVersionReader,
        BaseSaaSContext $baseSaaSContext,
        array $data = []
    ) {
        $this->attributeMetadata = $attributeMetadata;
        $this->toolbar = $toolbar;
        $this->metricsModel = $metricsModel;
        $this->serializer = $serializer;
        $this->moduleVersionReader = $moduleVersionReader;
        $this->baseSaasContext = $baseSaaSContext;
        parent::__construct($context, $data);
    }

    /**
     * Get page size limit.
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return (int) $this->toolbar->getLimit();
    }

    /**
     * Generate random unique UUID
     *
     * @return string
     * @throws Exception
     */
    public function generateUUID(): string
    {
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }

    /**
     * Get attributes from response.
     *
     * @return string|null
     */
    public function getAttributes(): ?string
    {
        $searchResponse = $this->metricsModel->getResponse();

        if ($searchResponse === null) {
            return null;
        }

        $searchResponseJson = $this->serializer->unserialize($searchResponse);
        $facets = $searchResponseJson["data"]["productSearch"]["facets"];
        $attributeCodes = array_column($facets, "attribute");

        return $this->serializer->serialize($this->attributeMetadata->getAttributesMetadata($attributeCodes));
    }

    /**
     * Get search request from metrics model.
     *
     * @return string|null
     */
    public function getSearchRequest(): ?string
    {
        return $this->metricsModel->getRequest();
    }

    /**
     * Get search response from metrics model.
     *
     * @return string|null
     */
    public function getSearchResponse(): ?string
    {
        return $this->metricsModel->getResponse();
    }

    /**
     * Get metrics url from config.
     *
     * @return string
     */
    public function getMetricsUrl(): string
    {
        return (string) $this->_scopeConfig->getValue(
            self::METRICS_URL,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Calculate and return execution time.
     *
     * @return float
     */
    public function getExecutionTime(): float
    {
        return ($this->metricsModel->getEndTime() - $this->metricsModel->getStartTime()) / 1000000;
    }

    /**
     * Get module version
     *
     * @return string|null
     */
    public function getModuleVersion(): ?string
    {
        return $this->moduleVersionReader->getVersion();
    }

    /**
     * Get customer group code.
     *
     * @return string
     */
    public function getCustomerGroupCode(): string
    {
        return $this->baseSaasContext->getCustomerGroupCode();
    }
}
