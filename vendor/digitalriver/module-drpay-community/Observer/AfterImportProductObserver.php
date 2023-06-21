<?php
/**
 * Observer to save catalog sync data after product import successfully
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Logger\Logger as DrLogger;
use Digitalriver\DrPay\Model\CatalogSyncRepository;
use Digitalriver\DrPay\Model\ResourceModel\CatalogSync;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Save Product Attribute to db_catalog_sync_queue table after import product done.
 */
class AfterImportProductObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $drConfig;

    /**
     * @var CatalogSync
     */
    private $catalogSyncResource;

    /**
     * @var DrLogger
     */
    private $drLogger;

    /**
     * @var CatalogSyncRepository;
     */
    private $catalogSyncRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    protected $storeRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    public $websiteRepository;


    /**
     * AfterImportProductObserver constructor.
     * @param Config $drConfig
     * @param DrLogger $drLogger
     * @param CatalogSyncRepository $catalogSyncRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CatalogSync $catalogSyncResource
     */
    public function __construct(
        Config $drConfig,
        DrLogger $drLogger,
        CatalogSyncRepository $catalogSyncRepository,
        ProductRepositoryInterface $productRepository,
        CatalogSync $catalogSyncResource,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->drConfig = $drConfig;
        $this->drLogger = $drLogger;
        $this->catalogSyncRepository = $catalogSyncRepository;
        $this->productRepository = $productRepository;
        $this->catalogSyncResource = $catalogSyncResource;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->jsonSerializer = $jsonSerializer;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository= $storeRepository;
    }

    /**
     * Action after data import. Save updated attribute value to dr_catalog_sync_queue table.
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->drConfig->getIsEnabled()) {
            return;
        }
        $adapter = $observer->getEvent()->getAdapter();

        if ($products = $observer->getEvent()->getBunch()) {
            $addToSyncProducts = []; 
            try {
                foreach ($products as $product) {
                    $productId = (int)$adapter->getNewSku($product[ImportProduct::COL_SKU])['entity_id']; 
                    $productData = $this->productRepository->getById($productId); 
                    /** Skip for the bundle and grouped product type */
                    $productTypeId = $productData->getTypeId(); 
                    if ($productTypeId === BundleType::TYPE_CODE || $productTypeId === Grouped::TYPE_CODE) { 
                        continue; 
                    }
                    // if there is a store view code defined
                    if (isset($product["store_view_code"]) && !empty($product["store_view_code"])) { 
                        $storeId = $this->storeRepository->get($product["store_view_code"])->getId();
                        $productAttribute = $this->catalogSyncRepository->createMassRequestData($product, $productData, (int)$storeId);
                        // add data to array to avoid duplicate entries
                        $addToSyncProducts[$productId][$storeId] = $productAttribute; 
                    } else if(isset($product["product_websites"]) && !empty($product["product_websites"])){
                        $productWebsites = $product["product_websites"];
                        $productWebsitesArray = explode(',', $productWebsites);
                        foreach($productWebsitesArray as $key){
                            $websiteId = $this->websiteRepository->get($key)->getId();
                            $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
                            foreach($storeIds as $storeId) {
                                $productAttribute = $this->catalogSyncRepository->createMassRequestData($product, $productData, (int)$storeId); 
                                $addToSyncProducts[$productId][$storeId] = $productAttribute; 
                            }
                        }
                    } else{
                        $productWebsites = $productData->getWebsiteIds();
                        foreach($productWebsites as $key){
                            $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($key);
                            foreach($storeIds as $storeId) {
                                $productAttribute = $this->catalogSyncRepository->createMassRequestData($product, $productData, (int)$storeId); 
                                $addToSyncProducts[$productId][$storeId] = $productAttribute; 
                            }
                        }
                    }
                } 
            } catch (NoSuchEntityException $e) { 
                $this->drLogger->error(sprintf('Import Product Id %s Error', $productId), [$e->getMessage()]); 
            } 
            // add all products to sync queue
            foreach ($addToSyncProducts as $productId => $addToSyncProduct) {
                foreach ($addToSyncProduct as $productAttribute) {
                    $this->catalogSyncRepository->saveCatalogSync($productId, $productAttribute); 
                } 
            } 
        }
    }
}
