<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearch\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\LiveSearch\Model\MerchantRegistryClient;
use Magento\ServicesId\Model\ServicesConfigInterface;

class RecurringData implements InstallDataInterface
{
    /**
     * @var MerchantRegistryClient
     */
    private MerchantRegistryClient $merchantRegistryClient;

    /**
     * @var ServicesConfigInterface
     */
    private ServicesConfigInterface $servicesConfig;

    /**
     * @param MerchantRegistryClient $merchantRegistryClient
     * @param ServicesConfigInterface $servicesConfig
     */
    public function __construct(
        MerchantRegistryClient $merchantRegistryClient,
        ServicesConfigInterface $servicesConfig
    ) {
        $this->merchantRegistryClient = $merchantRegistryClient;
        $this->servicesConfig = $servicesConfig;
    }

    /**
     * Register live search merchant on setup:upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!empty($this->servicesConfig->getEnvironmentId())) {
            $this->merchantRegistryClient->register($this->servicesConfig->getEnvironmentId());
        }
    }
}
