<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Api;

use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\ServicesConnector\Exception\PrivateKeySignException;

/**
 * Interface for service client to communicate with SaaS.
 */
interface ServiceClientInterface
{
    /**
     * Execute call to SaaS service
     *
     * @param array $headers
     * @param string $path
     * @param string $data
     * @return array
     * @throws KeyInvalidException
     * @throws ApiException
     */
    public function request(array $headers, string $path, string $data = ''): array;

    /**
     * Validate the API Gateway Key
     *
     * @return bool
     *
     * @throws KeyNotFoundException
     * @throws \InvalidArgumentException
     * @throws PrivateKeySignException
     */
    public function isApiKeyValid(): bool;
}
