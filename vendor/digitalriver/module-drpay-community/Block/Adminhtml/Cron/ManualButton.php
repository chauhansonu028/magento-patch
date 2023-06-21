<?php
/**
 * Manual Cron Run Controller
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Adminhtml\Cron;

use Magento\Cms\Block\Adminhtml\Block\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Manual Button in grid
 */
class ManualButton extends GenericButton implements ButtonProviderInterface
{
    private const MANUAL_CRON_JOB_URL = 'drpay/sync/manual';

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Manual Sync To Digital River'),
            'class' => 'primary',
            'on_click' => sprintf("window.location.href = '%s';", $this->getManualUrl()),
            'sort_order' => 20,
        ];
    }

    /**
     * URL to send manual requests.
     *
     * @return string
     */
    public function getManualUrl(): string
    {
        return $this->getUrl(self::MANUAL_CRON_JOB_URL);
    }
}
