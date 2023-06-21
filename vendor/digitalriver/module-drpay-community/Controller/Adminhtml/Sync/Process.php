<?php
/**
 * Manual Cron Process Controller
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\Adminhtml\Sync;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Model\Jobs\CatalogSync;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class for Catalog Sync Manual Cron Process
 */
class Process extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Digitalriver_DrPay::catalog_sync_grid';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CatalogSync
     */
    protected $catalogSyncCron;

    /**
     * @var Config
     */
    private $drConfig;

    /**
     * Process constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CatalogSync $catalogSyncCron
     * @param Config $drConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CatalogSync $catalogSyncCron,
        Config $drConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->catalogSyncCron = $catalogSyncCron;
        $this->drConfig = $drConfig;
        parent::__construct($context);
    }

    /**
     * Catalog Sync list action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->drConfig->getIsEnabled()) {
            $response = [
                'status' => 'error',
                'message' => __('Digital River API Setting Disabled.')
            ];
            return $resultJson->setData($response);
        }
        
        try {
            $beforeSyncItemCount = $this->getSyncTotalItems();
            $this->catalogSyncCron->runCron();
            $afterSyncItemCount = $this->getSyncTotalItems();
            $manualSyncLimit = $beforeSyncItemCount-$afterSyncItemCount;
            $response = [
                'status' => 'success',
                'message' => __(
                    "%1 Products have synced successfully and %2 is remaining to sync.",
                    $manualSyncLimit,
                    $afterSyncItemCount
                )
            ];
        } catch (Exception $exception) {
            $response = [
                'status' => 'error',
                'message' => $exception->getMessage()
            ];
        }

        return $resultJson->setData($response);
    }

    /**
     * Fetch total count of items
     *
     * @return int
     */
    public function getSyncTotalItems(): int
    {
        $syncItemTotalCount = 0;
        $syncItems = $this->catalogSyncCron->getCatalogSyncList();
        if ($syncItems) {
            $syncItemTotalCount = (int)$syncItems->getTotalCount();
        }
        return $syncItemTotalCount;
    }
}
