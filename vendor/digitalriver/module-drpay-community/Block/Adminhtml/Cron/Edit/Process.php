<?php
/**
 * Manual Cron Run Controller
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Adminhtml\Cron\Edit;

use Digitalriver\DrPay\Model\Jobs\CatalogSync;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Message\ManagerInterface;

class Process extends Template
{
    private const SYNC_PROCESS_URL = 'drpay/sync/process';

    private const SYNC_REDIRECT_URL = 'drpay/sync/index';

    /**
     * Set the Template file for the class
     * @var string
     */
    protected $_template = 'cron/process.phtml';

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CatalogSync
     */
    protected $catalogSyncCron;

    /**
     * Process constructor.
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param CatalogSync $catalogSyncCron
     * @param array $data
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        CatalogSync $catalogSyncCron,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->catalogSyncCron = $catalogSyncCron;
        parent::__construct($context, $data);
    }

    /**
     * Get Total Pending Rows to Sync
     *
     * @return int
     */
    public function getTotalRecordsCount(): int
    {
        $records = $this->catalogSyncCron->getCatalogSyncList();
        return (int)$records->getTotalCount();
    }

    /**
     * Get Ajax Url
     *
     * @return string
     */
    public function getProgressUrl(): string
    {
        return $this->getUrl(self::SYNC_PROCESS_URL);
    }

    /**
     * Get Ajax Url
     *
     * @return string
     */
    public function getSyncRedirectUrl(): string
    {
        return $this->getUrl(self::SYNC_REDIRECT_URL);
    }
}
