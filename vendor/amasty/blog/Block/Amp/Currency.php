<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Amp;

class Currency extends \Magento\Directory\Block\Currency
{
    public function getStoreUrlAmp(string $code): string
    {
        return $this->_urlBuilder->getUrl('directory/currency/switch', ['currency' => $code]);
    }
}
