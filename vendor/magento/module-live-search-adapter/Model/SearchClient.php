<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\LiveSearch\Api\ApiException;
use Magento\LiveSearch\Api\KeyInvalidException;
use Magento\LiveSearch\Api\ServiceClient;

/**
 * Class SearchClient
 *
 * Makes request to Search SaaS
 */
class SearchClient
{
    /**
     * Config path
     */
    private const BACKEND_PATH = 'live_search_adapter/backend_path';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var SearchScopeResolver
     */
    private SearchScopeResolver $scopeResolver;

    /**
     * @var ServiceClient
     */
    private ServiceClient $serviceClient;

    /**
     * @param ScopeConfigInterface $config
     * @param SearchScopeResolver $scopeResolver
     * @param ServiceClient $serviceClient
     */
    public function __construct(
        ScopeConfigInterface $config,
        SearchScopeResolver $scopeResolver,
        ServiceClient $serviceClient
    ) {
        $this->config = $config;
        $this->scopeResolver = $scopeResolver;
        $this->serviceClient = $serviceClient;
    }

    /**
     * Execute request to SaaS.
     *
     * @param string $body
     *
     * @return array
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ApiException
     * @throws KeyInvalidException
     */
    public function request(string $body): array
    {
        $headers = $this->getHeaders();
        $path = $this->config->getValue(self::BACKEND_PATH);

        return $this->serviceClient->request($headers, $path, $body);
    }

    /**
     * Get headers for the request.
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getHeaders(): array
    {
        return [
            'Magento-Website-Code' => $this->scopeResolver->getWebsiteCode(),
            'Magento-Store-Code' => $this->scopeResolver->getStoreCode(),
            'Magento-Store-View-Code' => $this->scopeResolver->getStoreViewCode(),
        ];
    }
}
