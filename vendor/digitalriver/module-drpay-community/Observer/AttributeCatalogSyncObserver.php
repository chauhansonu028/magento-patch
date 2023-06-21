<?php
/**
 * Save Record to catalog sync table after add/update product
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Model\CatalogSyncRepository;
use Digitalriver\DrPay\Helper\Config;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Digitalriver\DrPay\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Save Product Attribute to db_catalog_sync_queue table after add/update product
 */
class AttributeCatalogSyncObserver implements ObserverInterface
{
    /**
     * @var CatalogSyncRepository
     */
    private $catalogSyncRepository;

    /**
     * @var Config
     */
    private $drConfig;

    /**
     * @var Logger
     */
    private $logger;

     /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * AttributeCatalogSyncObserver constructor.
     * @param Config $drConfig
     * @param CatalogSyncRepository $catalogSyncRepository
     */
    public function __construct(
        Config $drConfig,
        Logger $logger,
        Json $jsonSerializer,
        StoreManagerInterface $storeManager,
        CatalogSyncRepository $catalogSyncRepository,
        StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
        $this->drConfig = $drConfig;
        $this->catalogSyncRepository = $catalogSyncRepository;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->storeManager = $storeManager;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * Process source items during product saving via controller.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {  
        if (!$this->drConfig->getIsEnabled()) {
            return;
        }
        
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct(); 
        
        if($product->getStoreId()){
            $productAttribute = $this->catalogSyncRepository->createRequestData($product,(int)$product->getStoreId());
            $productId = (int)$product->getId();
            $this->catalogSyncRepository->saveCatalogSync($productId, $productAttribute);
            return;
        }
        $websiteIds = $product->getWebsiteIds();  

        foreach($websiteIds as $key => $val) {
            $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($val);
            foreach($storeIds as $storeId){
                /** Skip for the bundle and grouped product type */
                $productTypeId = $product->getTypeId();
                if ($productTypeId === BundleType::TYPE_CODE || $productTypeId === Grouped::TYPE_CODE) {
                    return;
                }
                $productAttribute = $this->catalogSyncRepository->createRequestData($product,(int)$storeId);
                $productId = (int)$product->getId();

                $this->catalogSyncRepository->saveCatalogSync($productId, $productAttribute);
            }    
        }     
    }
}
