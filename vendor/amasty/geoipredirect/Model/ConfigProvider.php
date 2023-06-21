<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'amgeoipredirect/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const EXCEPTED_URLS = 'restriction/excepted_urls';

    public function getExceptedUrls(): array
    {
        return explode(
            PHP_EOL,
            (string)$this->getValue(self::EXCEPTED_URLS)
        );
    }
}
