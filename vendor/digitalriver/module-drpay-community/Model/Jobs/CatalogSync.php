<?php
/**
 * Catalog Sync Cron Execute
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Jobs;

use Digitalriver\DrPay\Api\CatalogSyncRepositoryInterface;
use Digitalriver\DrPay\Api\Data\CatalogSyncInterface;
use Digitalriver\DrPay\Api\Data\CatalogSyncInterfaceFactory;
use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Helper\Data as DrHelper;
use Digitalriver\DrPay\Logger\Logger;
use Digitalriver\DrPay\Model\CatalogSyncRepository as SyncRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * CatalogSync Cron Model
 */
class CatalogSync
{
    private const RESPONSE_CODE_400 = 400;
    private const RESPONSE_CODE_500 = 500;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EmailNotification
     */
    private $emailNotification;

    /**
     * @var Config
     */
    private $drConfig;

    /**
     * @var DrHelper
     */
    private $drHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CatalogSyncRepositoryInterface
     */
    private $catalogSyncRepository;

    /**
     * @var CatalogSyncInterfaceFactory
     */
    private $catalogSyncFactory;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * Core session model
     *
     * @var Generic
     */
    protected $cronSession;

    /**
     * @var Json
     */
    private $jsonSerialzier;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        EmailNotification $emailNotification,
        Config $drConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CatalogSyncRepositoryInterface $catalogSyncRepository,
        CatalogSyncInterfaceFactory $catalogSyncFactory,
        SortOrderBuilder $sortOrderBuilder,
        SerializerInterface $serialize,
        Generic $cronSession,
        DrHelper $drHelper,
        Logger $logger,
        Json $jsonSerialzier,
        StoreManagerInterface $storeManager
    ) {
        $this->emailNotification = $emailNotification;
        $this->drConfig = $drConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->catalogSyncRepository = $catalogSyncRepository;
        $this->catalogSyncFactory = $catalogSyncFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->serialize = $serialize;
        $this->cronSession = $cronSession;
        $this->drHelper = $drHelper;
        $this->logger = $logger;
        $this->jsonSerialzier = $jsonSerialzier;
        $this->storeManager = $storeManager;
    }

    /**
     * Run Catalog Sync Cron Jobs
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(): void
    {
        $this->runCron();
    }

    /**
     * Get List by search criteria
     *
     * @return SearchResultsInterface|null
     */
    public function getCatalogSyncList(): ?SearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SyncRepository::STATUS, SyncRepository::STATUS_PENDING)
            ->create();

        $batchSizeLimit = $this->getBunchSizeLimit();
        $searchCriteria->setPageSize($batchSizeLimit);

        /** @var SortOrder $sortOrderBuilder */
        $sortOrder = $this->sortOrderBuilder->setField('entity_id')
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $searchCriteria->setSortOrders([$sortOrder]);

        return $this->getList($searchCriteria);
    }

    /**
     * Run Cron Job to sync catalog sku with DR API Call
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function runCron(): void
    {
        if (!$this->drConfig->getIsEnabled()) {
            return;
        }
        $syncItems = $this->getCatalogSyncList();
        if (!$syncItems->getTotalCount()) {
            return;
        }
        
        $isDebugEnable = $this->drConfig->isDebugModeEnable();
        $data = $failedItemIds = [];
        $syncDone = [];
        foreach ($syncItems->getItems() as $item) {
            $requestData = $this->serialize->unserialize($item->getRequestData());
            $storeId = $requestData["metadata"]["storeId"] ?? null;
            $storecode = $this->storeManager->getStore($storeId)->getCode(); 
            $catalogSyncId = (int)$item->getId();
            $productId = (int)$item->getProductId();
            $sku = $item->getProductSku();
            $apiKey = $this->drConfig->getSecretKey($storecode);
            $hash = hash('md5', $sku.$apiKey); 
            
            if(!$this->drConfig->isCatalogSyncEnable($storeId)){
                $catalogSync = $this->catalogSyncFactory->create();
                $catalogSync->setSkuHash($hash);
                $catalogSync->setId($catalogSyncId);
                $catalogSync->setProductId($productId);
                $catalogSync->setResponseData("Catalog sync is disabled for this store");
                $status = SyncRepository::STATUS_PENDING;
                $catalogSync->setStatus($status);
                $this->saveCatalogSyncResponse($catalogSync);
                continue;
            };

            if (is_array($requestData)) {
                $data[SyncRepository::ECCN] = $requestData[SyncRepository::ECCN];
                $data[SyncRepository::TAX_CODE] = $requestData[SyncRepository::TAX_CODE];
                $data[SyncRepository::COUNTRY_OF_ORIGIN] = $requestData[SyncRepository::COUNTRY_OF_ORIGIN];
                $data[SyncRepository::NAME] = $requestData[SyncRepository::NAME];
                $data[SyncRepository::FULFILL] = $requestData[SyncRepository::FULFILL];
                $data[SyncRepository::PART_NUMBER] = $requestData[SyncRepository::PART_NUMBER];
                $data[SyncRepository::HS_CODE] = $requestData[SyncRepository::HS_CODE];
                $data[SyncRepository::SKU_GROUP_ID] = $requestData[SyncRepository::SKU_GROUP_ID] ?? null;
                $data[SyncRepository::WEIGHT] = $requestData[SyncRepository::WEIGHT] ?? null;
                $data[SyncRepository::WEIGHT_UNIT] = $requestData[SyncRepository::WEIGHT_UNIT] ?? null;
                $data["metadata"]["storeId"] = $requestData["metadata"]["storeId"] ?? null;
            }
            if ($isDebugEnable) {
                $this->logger->info('Catalog SKU ID Value ' . $sku);
                $this->logger->info('Catalog SKU API Call Payload ' . $this->jsonSerialzier->serialize($data));
            }

            
            // if the sync is done successfully, just save data, else run sync
            if (array_key_exists($hash,$syncDone) && !in_array($productId, $failedItemIds)) {
                $catalogSync = $this->catalogSyncFactory->create();
                $catalogSync->setSkuHash($hash);
                $catalogSync->setId($catalogSyncId);
                $catalogSync->setProductId($productId);
                $catalogSync->setSyncedToDrAt($syncDone[$hash]['setSyncedToDrAt']);
                if($syncDone[$hash]['status'] == "Fail"){
                    $catalogSync->setResponseData($syncDone[$hash]["responseData"]);
                }else{
                    $catalogSync->setResponseData("SKU already synced");
                }
                $catalogSync->setStatus($syncDone[$hash]['status']);
                $this->saveCatalogSyncResponse($catalogSync); 
            } else {
                $syncDone[$hash] = $this->syncCatalog($catalogSyncId, $productId, $sku, $data, $hash);
            }
        }

        /** Check Failed Items Status Exists, If Yes, send errors mail with ids. */
        if (count($failedItemIds) > 0) {
            $productStatusFailed = array_unique($failedItemIds);
            if ($isDebugEnable) {
                $this->logger->error('Failed Response for Product Id, ', [$productStatusFailed]);
            }
            $this->emailNotification->sendErrorsMail($productStatusFailed);
        }
    }

    private function syncCatalog($catalogSyncId, $productId, $sku, $data, $hash) {
        $result = [];
        $storeId = $data["metadata"]["storeId"];
        $isDebugEnable = $this->drConfig->isDebugModeEnable();
        $catalogSync = $this->catalogSyncFactory->create();
        $catalogSync->setId($catalogSyncId);
        $catalogSync->setProductId($productId);
        $syncTime = (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
        $catalogSync->setSyncedToDrAt($syncTime);
        $response = $this->drHelper->setSku($sku, $data);

        if ($isDebugEnable) {
            $this->logger->info('Response Catalog SKU API ' . $this->jsonSerialzier->serialize($response));
        }

        $statusCode = $response['statusCode'];
        $responseData = $this->serialize->serialize($response);
        $catalogSync->setResponseData($responseData);
        $result['catalogSyncId'] = $catalogSyncId;
        $result['productId'] = $productId;
        $result['setSyncedToDrAt'] = $syncTime;
        $result['responseData'] = $responseData;
        
        if (isset($response['success']) && !empty($response['success'])) {
            $catalogSync->setStatus(SyncRepository::STATUS_SUCCESS);
            $catalogSync->setSkuHash($hash);
            $result['status'] = SyncRepository::STATUS_SUCCESS;
        } else {
            $status = SyncRepository::STATUS_PENDING;
            if ($statusCode >= self::RESPONSE_CODE_400 &&
                $statusCode < self::RESPONSE_CODE_500) {
                $status = SyncRepository::STATUS_FAIL;
                $failedItemIds[] = $productId;
            }
            $catalogSync->setStatus($status);
            $result['status'] = $status;
        }
        /** Save Catalog Sync Entry */
        $this->saveCatalogSyncResponse($catalogSync); 
        return $result;
    }

    /**
     * Save Catalog Sync DR Response
     *
     * @param CatalogSyncInterface $catalogSync
     * @return void
     */
    private function saveCatalogSyncResponse(CatalogSyncInterface $catalogSync): void
    {
        try {
            $this->catalogSyncRepository->save($catalogSync);
        } catch (LocalizedException $exception) {
            $this->logger->error('Cant save sync record.', [$exception]);
        }
    }

    /**
     * Save Catalog Sync DR Response
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface|null
     */
    private function getList(SearchCriteriaInterface $searchCriteria): ?SearchResultsInterface
    {
        $syncItems = null;
        try {
            $syncItems = $this->catalogSyncRepository->getList($searchCriteria);
        } catch (LocalizedException $exception) {
            $this->logger->error('Catalog Sync get list fetch error', [$exception]);
        }

        return $syncItems;
    }

    /**
     * Get Bunch size limit
     * @return int
     */
    public function getBunchSizeLimit(): int
    {
        return (int)$this->drConfig->getBatchSizeLimit();
    }
}