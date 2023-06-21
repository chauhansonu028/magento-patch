<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Plugin;

use Magento\CatalogDataExporter\Model\Provider\Product\Displayable as ProductDisplayable;
use Magento\CatalogPermissions\App\Backend\Config as PermissionsConfig;
use Magento\QueryXml\Model\QueryProcessor;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface as LoggerInterface;

/**
 * Plugin for fetching products correct displayable status if category permissions are set
 */
class Displayable
{
    private QueryProcessor $queryProcessor;
    private PermissionsConfig $permissionsConfig;
    private LoggerInterface $logger;

    /**
     * @param QueryProcessor $queryProcessor
     * @param PermissionsConfig $permissionsConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueryProcessor $queryProcessor,
        PermissionsConfig $permissionsConfig,
        LoggerInterface $logger
    ) {
        $this->queryProcessor = $queryProcessor;
        $this->permissionsConfig = $permissionsConfig;
        $this->logger = $logger;
    }

    /**
     * Check displayable status taking category permissions into account after default displayable is calculated
     *
     * @param ProductDisplayable $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(ProductDisplayable $subject, array $result): array
    {
        try {
            if ($this->permissionsConfig->isEnabled()) {
                $queryArguments = [];
                foreach ($result as $value) {
                    $productId = $value['productId'];
                    $storeViewCode = $value['storeViewCode'];
                    $queryArguments['productIds'][$productId] = $productId;
                    $queryArguments['storeViewCode'][$storeViewCode] = $storeViewCode;
                }
                $queryArguments['customerGroupFilter'] = 0;
                $displayable = [];
                $cursor = $this->queryProcessor->execute('productDisplayableOverride', $queryArguments);

                while ($row = $cursor->fetch()) {
                    if ((int)$row['displayable'] === -1) {
                        $displayable[$row['productId']] = true;
                    } else {
                        $displayable[$row['productId']] = false;
                    }
                }

                foreach ($result as $key => $value) {
                    if (isset($displayable[$value['productId']])) {
                        $result[$key]['displayable'] = $displayable[$value['productId']];
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }
        return $result;
    }
}
