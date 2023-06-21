<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Model\Provider;

use Magento\Framework\ObjectManagerInterface;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface as LoggerInterface;

/**
 * Class ProductOverrides
 */
class ProductOverrides
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $overrideProviders;

    /**
     * ProductOverrides constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $overrideProviders
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        array $overrideProviders = []
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->overrideProviders = $overrideProviders;
    }

    /**
     * @param array $values
     * @return array
     */
    public function get(array $values) : array
    {
        $output = [];
        $mainProviderOutput = [];
        try {
            foreach ($this->overrideProviders as $providerData) {
                $providerClassName = $providerData['class_name'];
                $provider = $this->objectManager->get($providerClassName);
                if (true === $providerData['is_main']) {
                    $mainProviderOutput[] = $provider->get($values);
                } else {
                    $output[] = $provider->get($values);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception, ['exception' => $exception]);
            throw new UnableRetrieveData('Unable to retrieve product override data');
        }
        if (!empty(array_filter($mainProviderOutput)) || !empty(array_filter($output))) {
            $output = \array_replace_recursive(
                array_intersect_key(
                    \array_replace_recursive(...($output ?: [[]])),
                    \array_replace_recursive(...$mainProviderOutput)
                ),
                ...$mainProviderOutput
            );
        }

        return !empty(array_filter($output)) ? $output: [];
    }
}
