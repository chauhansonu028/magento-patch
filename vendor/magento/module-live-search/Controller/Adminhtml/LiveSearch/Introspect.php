<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Controller\Adminhtml\LiveSearch;

use Magento\LiveSearch\Model\AbstractProxyController;

/**
 * Controller that proxies requests for search service graphql introspection.
 */
class Introspect extends AbstractProxyController
{
    /**
     * Config paths
     */
    public const BACKEND_PATH = 'live_search/backend_introspect_path';
}
