<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Model\RedirectUrl;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\RedirectDataGenerator;

class UrlProcessor
{
    public const STORE_SWITCH_URL = '/stores/store/switch/';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $redirectUrl
     * @param RequestInterface $request
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @return string
     */
    public function updateRedirectUrlQueryParams(
        string $redirectUrl,
        RequestInterface $request,
        StoreInterface $fromStore,
        StoreInterface $targetStore
    ): string {
        $queryParams = [
            '___store' => $targetStore->getCode(),
            '___from_store' => $fromStore->getCode()
        ];
        if ($this->isStoreSwitchRequest($request)) {
            $queryParams += $this->getRedirectQueryParams($fromStore, $targetStore, $redirectUrl);
        }

        if ($origQueryStr = $this->parseUrl($redirectUrl, PHP_URL_QUERY)) {
            $queryParams += $this->parseQuery($origQueryStr);
            $redirectUrl = str_replace('?' . $origQueryStr, '', $redirectUrl);
        }
        $redirectUrl .= '?' . $this->buildQuery($queryParams);

        return $redirectUrl;
    }

    /**
     * @param string $url
     * @param int $component
     * @return array|false|int|string|null
     */
    public function parseUrl(string $url, int $component = -1)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return parse_url($url, $component);
    }

    /**
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     * @return array
     */
    private function getRedirectQueryParams(
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl
    ): array {
        if (!class_exists(RedirectDataGenerator::class)) {
            return [];
        }

        $redirectDataGenerator = $this->objectManager->create(RedirectDataGenerator::class);
        $contextFactory = $this->objectManager->create(ContextInterfaceFactory::class);
        $redirectData = $redirectDataGenerator->generate(
            $contextFactory->create(
                [
                    'fromStore' => $fromStore,
                    'targetStore' => $targetStore,
                    'redirectUrl' => $redirectUrl
                ]
            )
        );

        return [
            'data' => $redirectData->getData(),
            'time_stamp' => $redirectData->getTimestamp(),
            'signature' => $redirectData->getSignature()
        ];
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function isStoreSwitchRequest(RequestInterface $request): bool
    {
        return stripos((string)$request->getPathInfo(), self::STORE_SWITCH_URL) !== false;
    }

    /**
     * @param string $queryStr
     * @return array
     */
    private function parseQuery(string $queryStr): array
    {
        $result = [];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str($queryStr, $result);

        return $result;
    }

    /**
     * @param array $queryParams
     * @return string
     */
    private function buildQuery(array $queryParams): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
    }
}
