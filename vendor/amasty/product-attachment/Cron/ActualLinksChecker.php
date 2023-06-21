<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Cron;

use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\Healthcheck\NotificationProcessor;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\StoreManagerInterface;

class ActualLinksChecker
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var NotificationProcessor
     */
    private $notificationProcessor;

    /**
     * @var AppState
     */
    private $appState;

    public function __construct(
        StoreManagerInterface $storeManager,
        NotificationProcessor $notificationProcessor,
        AppState $appState,
        ConfigProvider $configProvider
    ) {
        $this->storeManager = $storeManager;
        $this->notificationProcessor = $notificationProcessor;
        $this->appState = $appState;
        $this->configProvider = $configProvider;
    }

    public function execute(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$this->notificationProcessor, 'process']
        );
    }

    /**
     * Setting is needed to be enabled at least in one website scope
     * or in default scope with at least one website scope which inherits default scope value
     * to process further
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            if ($this->configProvider->isHealthcheckEnabled((int)$website->getId())) {
                return true;
            }
        }

        return false;
    }
}
