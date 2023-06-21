<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearchMetrics\Plugin;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\LiveSearchMetrics\Model\MetricsModel;
use Magento\LiveSearchAdapter\Model\SearchClient;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class MetricsPlugin
{
    /**
     * Name of the search request id cookie
     */
    private const COOKIE_NAME = 'requestId';

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var MetricsModel
     */
    private MetricsModel $metrics;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param MetricsModel $metrics
     */
    public function __construct(CookieManagerInterface $cookieManager, MetricsModel $metrics)
    {
        $this->cookieManager = $cookieManager;
        $this->metrics = $metrics;
    }

    /**
     * "Before" interceptor for \Magento\LiveSearchAdapter\Model\SearchClient::request method
     *
     * @param SearchClient $subject
     * @param string $body
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRequest(SearchClient $subject, string $body): ?array
    {
        $this->metrics->saveStartTime();
        $this->metrics->saveRequest($body);
        return null;
    }

    /**
     * "After" interceptor for \Magento\LiveSearchAdapter\Model\SearchClient::request method
     *
     * @param SearchClient $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRequest(SearchClient $subject, array $result): array
    {
        $this->metrics->saveEndTime();
        $this->metrics->saveResponse($result);
        return $result;
    }

    /**
     * "After" interceptor for \Magento\LiveSearchAdapter\Model\SearchClient::getHeaders method
     *
     * @param SearchClient $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetHeaders(SearchClient $subject, array $result): array
    {
        $cookie = $this->cookieManager->getCookie(self::COOKIE_NAME);

        if (!empty($cookie)) {
            $result["X-Request-Id"] = $cookie;
        }

        return $result;
    }
}
