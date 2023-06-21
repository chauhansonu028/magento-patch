<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearch\Model;

/**
 * Reads version of the module.
 */
class ModuleVersionReader
{
    /**
     * @var string|null
     */
    private static ?string $version = null;

    /**
     * Return version of the module from compose.json file.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        if (null === self::$version) {
            $version = 'UNKNOWN';
            $composerFile = __DIR__ . '/../composer.json';

            //phpcs:disable
            if (file_exists($composerFile)) {
                $composerData = json_decode(file_get_contents($composerFile), true);
                //phpcs:enable
                if (is_array($composerData) && isset($composerData['version'])) {
                    $version = $composerData['version'];
                }
            }
            self::$version = $version;
        }

        return self::$version;
    }
}
