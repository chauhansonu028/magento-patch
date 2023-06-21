<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

class DummyAdapter implements AdapterInterface
{
    public function query(RequestInterface $request)
    {
        // TODO: Implement query() method.
    }
}
