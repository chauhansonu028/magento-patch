<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

/**
 * Interface HttpClientInterface
 **/
interface HttpClientInterface extends \Magento\Framework\HTTP\ClientInterface
{
    /**
     * Make PUT request
     *
     * The parameters should be a string when making a JSON or XML request.
     *
     * @param string $uri
     * @param array|string $params
     */
    public function put(string $uri, $params): void;
}
