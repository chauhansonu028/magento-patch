<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LiveSearch\Plugin;

use Magento\Framework\App\Config\Value;
use Magento\LiveSearch\Model\MerchantRegistryClient;

/**
 * Plugin to register merchant for live search on config save
 * Triggered on setup:upgrade, app:config:import, config:set and config change via Admin UI
 */
class RegisterMerchantOnConfigValueSave
{
    /**
     * Config path for environment id
     */
    private const ENVIRONMENT_ID_CONFIG_PATH = 'services_connector/services_id/environment_id';

    /**
     * @var MerchantRegistryClient
     */
    private MerchantRegistryClient $merchantRegistryClient;

    /**
     * @param MerchantRegistryClient $merchantRegistryClient
     */
    public function __construct(
        MerchantRegistryClient $merchantRegistryClient
    ) {
        $this->merchantRegistryClient = $merchantRegistryClient;
    }

    /**
     * Register merchant if environment id has changed.
     *
     * AfterAfterSave is triggered when config saved via app:config:import and not afterSave
     *
     * @param Value $subject
     * @param Value $result
     * @return Value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(Value $subject, Value $result) : Value
    {
        $savedConfigPath = $result->getPath();
        if ($savedConfigPath === self::ENVIRONMENT_ID_CONFIG_PATH) {
            $environmentId = $result->getValue();
            // unable to use $result->isValueChanged() to minimize how often we make a call because it returns false
            // when triggered via app:config:import even when there is a change.
            if (!empty($environmentId)) {
                $this->merchantRegistryClient->register($environmentId);
            }
        }
        return $result;
    }
}
