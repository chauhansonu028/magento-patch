<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\ReportBuilder;

use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Advertise implements ArgumentInterface
{
    const REPORT_BUILDER_MODULE_NAME = 'Amasty_ReportBuilder';
    const PRODUCT_URL = 'https://amasty.com/custom-reports-for-magento-2.html';
    const ADV_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=blog_to_reports_builder_m2';

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function isReportBuilderDisabled(): bool
    {
        return !$this->moduleManager->isEnabled(self::REPORT_BUILDER_MODULE_NAME);
    }

    public function getAdvUrl(): string
    {
        return self::PRODUCT_URL . self::ADV_PARAMS;
    }
}
