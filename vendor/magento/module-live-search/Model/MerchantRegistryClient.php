<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Model;

use Magento\ServicesId\Model\ServicesClientInterface;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Psr\Log\LoggerInterface;

class MerchantRegistryClient
{
    private const PREMIUM_SEARCH_FEATURE = 'PREMIUM_SEARCH';

    /**
     * @var ServicesConfigInterface
     */
    private ServicesConfigInterface $servicesConfig;

    /**
     * @var ServicesClientInterface
     */
    private ServicesClientInterface $servicesClient;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ServicesConfigInterface $servicesConfig
     * @param ServicesClientInterface $servicesClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        ServicesConfigInterface $servicesConfig,
        ServicesClientInterface $servicesClient,
        LoggerInterface $logger
    ) {
        $this->servicesConfig = $servicesConfig;
        $this->servicesClient = $servicesClient;
        $this->logger = $logger;
    }

    /**
     * Call registry API to add Live Search feature
     *
     * @param string $environmentId
     */
    public function register(string $environmentId)
    {
        if ($this->servicesConfig->isApiKeySet() && !empty($environmentId)) {
            try {
                $response = $this->servicesClient->request('PUT', $this->getUrl($environmentId));
                if ($response
                    && !empty($response['status'])
                    && $response['status'] != 200
                    && !empty($response['message'])
                ) {
                    $this->logger->error('Unable to register for live search.', ['error' => $response['message']]);
                }
            } catch (\Exception $exception) {
                $this->logger->error('Unable to register for live search.', ['error' => $exception]);
            }
        }
    }

    /**
     * Build registry API url
     *
     * @param string $environmentId
     * @return string
     */
    private function getUrl(string $environmentId): string
    {
        $path = sprintf(
            'registry/environments/%s/feature/%s',
            $environmentId,
            self::PREMIUM_SEARCH_FEATURE
        );
        return $this->servicesConfig->getRegistryApiUrl($path);
    }
}
