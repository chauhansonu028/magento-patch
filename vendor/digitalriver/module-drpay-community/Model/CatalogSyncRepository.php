<?php
/**
 * Catalog Sync Model Repository
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\CatalogSyncRepositoryInterface;
use Digitalriver\DrPay\Api\Data\CatalogSyncInterface;
use Digitalriver\DrPay\Logger\Logger as DrLogger;
use Digitalriver\DrPay\Model\ResourceModel\CatalogSync as CatalogSyncResourceModel;
use Digitalriver\DrPay\Model\ResourceModel\CatalogSync\CollectionFactory as CollectionFactory;
use Digitalriver\DrPay\Model\ResourceModel\TaxTypes\CollectionFactory as TaxTypeCollectionFactory;
use Exception;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Downloadable\Model\Product\Type as DownloadableProduct;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class CatalogSyncRepository to perform CRUD Operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class CatalogSyncRepository implements CatalogSyncRepositoryInterface
{
    public const FULFILL = 'fulfill';
    public const ECCN = 'eccn';
    public const DR_ECCN_CODE = 'dr_eccn_code';
    public const TAX_CODE = 'taxCode';
    public const DR_TAX_CODE = 'dr_tax_type';
    public const NAME = 'name';
    public const PART_NUMBER = 'partNumber';
    public const SKU = 'sku';
    public const COUNTRY_OF_ORIGIN = 'countryOfOrigin';
    public const DR_COUNTRY_OF_ORIGIN = 'dr_country_of_origin';
    public const STATUS = 'status';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_FAIL = 'Fail';
    public const STATUS_SUCCESS = 'Success';
    public const META_DATA = 'metadata';
    public const HS_CODE = 'hsCode';
    public const DR_HS_CODE = 'dr_hs_code';
    public const SKU_GROUP_ID = 'skuGroupId';
    public const DR_SKU_GROUP_ID = 'dr_sku_group_id';
    public const WEIGHT = "weight";
    public const WEIGHT_UNIT = "weightUnit";
    
    /**
     * Track action for the given attribute value updates
     */
    public const UPDATE_ATTRIBUTES = ['name', 'dr_eccn_code', 'dr_tax_type', 'dr_country_of_origin', 'dr_hs_code'];

    /**
     * @var CatalogSyncResourceModel
     */
    protected $catalogSyncResourceModel;

    /**
     * @var CatalogSyncFactory
     */
    protected $catalogSyncFactory;

    /**
     * @var CollectionFactory
     */
    protected $catalogSyncCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DrLogger
     */
    private $drLogger;

    /**
     * @var SerializerInterface
     */
    private $serialize;

     /**
      * @var ScopeConfigInterface
      */
    private $_scopeConfig;

    /**
     * @var ProductAttributeInterface
     */
    private $attribute;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var TaxTypeCollectionFactory
     */
    private $taxTypeCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    private $updatedWeightUnit;

    /**
     * CatalogSyncRepository constructor.
     * @param CatalogSyncResourceModel $catalogSyncResourceModel
     * @param CatalogSyncFactory $catalogSyncFactory
     * @param CollectionFactory $catalogSyncCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SerializerInterface $serialize
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param DrLogger $drLogger
     * @param TaxTypeCollectionFactory $taxTypeCollection
     * @param ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $_scopeConfig
     */
    public function __construct(
        CatalogSyncResourceModel $catalogSyncResourceModel,
        CatalogSyncFactory $catalogSyncFactory,
        CollectionFactory $catalogSyncCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        SerializerInterface $serialize,
        ProductAttributeRepositoryInterface $attributeRepository,
        DrLogger $drLogger,
        TaxTypeCollectionFactory $taxTypeCollection,
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $_scopeConfig
    ) {
        $this->catalogSyncResourceModel = $catalogSyncResourceModel;
        $this->catalogSyncFactory = $catalogSyncFactory;
        $this->catalogSyncCollectionFactory = $catalogSyncCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->serialize = $serialize;
        $this->attributeRepository = $attributeRepository;
        $this->drLogger = $drLogger;
        $this->taxTypeCollection = $taxTypeCollection;
        $this->productRepository = $productRepository;
        $this->_scopeConfig = $_scopeConfig;
    }

    /**
     * Save Catalog Sync data
     *
     * @param CatalogSyncInterface $catalogSync
     *
     * @return void
     * @throws LocalizedException
     */
    public function save(CatalogSyncInterface $catalogSync): void
    {
        try {
            $this->catalogSyncResourceModel->save($catalogSync);
        } catch (Exception $exception) {
            $this->drLogger->critical("Catalog Sync saving error: ", [$exception->getMessage()]);
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * Load Catalog Sync data by given id
     *
     * @param int $syncId
     *
     * @return CatalogSyncInterface
     */
    public function getById(int $syncId): CatalogSyncInterface
    {
        $catalogSync = $this->catalogSyncFactory->create();
        $this->catalogSyncResourceModel->load($catalogSync, $syncId);
        if (!$catalogSync->getEntityId()) {
            $this->drLogger->alert("Catalog Sync with id $syncId does not exist.", []);
        }
        return $catalogSync;
    }

    /**
     * Find Catalog sync by SearchCriteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultsInterface
    {
        $collection = $this->catalogSyncCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var SearchResultsInterfaceFactory $searchResult */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Catalog sync record
     *
     * @param CatalogSyncInterface $catalogSync
     *
     * @return bool
     */
    public function delete(CatalogSyncInterface $catalogSync): bool
    {
        try {
            $this->catalogSyncResourceModel->delete($catalogSync);
        } catch (Exception $exception) {
            $this->drLogger->alert("Catalog Sync delete error", [$exception->getMessage()]);
        }
        return true;
    }

    /**
     * Delete Catalog sync by given Identity
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Save Catalog Sync Items data.
     *
     * @param int $productId
     * @param array $productAttribute
     * @return void
     */
    public function saveCatalogSync(int $productId, array $productAttribute): void
    {
        $catalogSync = $this->catalogSyncFactory->create();
        $catalogSync->setProductId($productId);
        $catalogSync->setProductSku($this->getSkuByID($productId));
        $catalogSync->setRequestData($this->serialize->serialize($productAttribute));
        $catalogSync->setResponseData(null);
        $catalogSync->setStatus(self::STATUS_PENDING);
        $catalogSync->setAddedToQueueAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        try {
            $this->save($catalogSync);
        } catch (LocalizedException $exception) {
            $this->drLogger->error(sprintf('Product Id %s with Add/Update Sync Error', $productId), [$exception]);
        }
    }

    /**
     * Check Product is in sync queue, if yes, get the id of that record.
     * @param int $productId
     * @return int
     */
    public function getSyncIdOfProductInQueue(int $productId): int
    {
        return $this->catalogSyncResourceModel->getCatalogSyncIdByProductId($productId);
    }

    /**
     * Create Request Data
     *
     * @param ProductInterface $product
     * @return array
     */
    public function createRequestData(ProductInterface $product,int $storeId): array
    {     
        $productAttribute = [];
        $productAttribute[self::FULFILL] = false;
        $productAttribute[self::PART_NUMBER] = $this->getPartNumber($product);
        $productAttribute[self::ECCN] = $product->getDrEccnCode();
        $productAttribute[self::TAX_CODE] = $product->getDrTaxType();
        $productAttribute[self::NAME] = $product->getName();
        $productAttribute[self::COUNTRY_OF_ORIGIN] = $product->getDrCountryOfOrigin();
        $productAttribute[self::HS_CODE] = !empty($product->getDrHsCode()) ? $product->getDrHsCode() : null;
        
        $productAttribute[self::SKU_GROUP_ID] = $product->getDrSkuGroupId() ?: null;
        $productAttribute["metadata"]["storeId"] = $storeId;
       
        $weightUnit = $this->getWeightUnit();
        switch ($weightUnit) {
            case "lbs":
            {
                $weightUnit = "lb";
                break;
            }
            case "kgs":
            {
                $weightUnit = "kg";
                break;
            }
            default:
            {
                $weightUnit = '';
            }
        }

        $productWeight = $product->getWeight();
        $productHasWeight = $product->getProductHasWeight();

        // if there is product weight and there's a valid weight unit
        if ($productHasWeight && $productWeight && $productWeight > 0 && !empty($weightUnit)) {
            $productAttribute[self::WEIGHT] = round((float)$productWeight, 2);
            $productAttribute[self::WEIGHT_UNIT] = $weightUnit;
        } else {
            $productAttribute[self::WEIGHT] = null;
            $productAttribute[self::WEIGHT_UNIT] = null;
        }

        $dataCollection = $this->taxTypeCollection->create()->getData();
        foreach ($dataCollection as $item) {
            if ($item['entity_id'] === $product->getDrTaxType()) {
                $productAttribute[self::TAX_CODE] = $item['dr_sabrixcode'];
            }
        }
        return $productAttribute;
    }

    public function getWeightUnit()
    {
        return $this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Create Request Data for the mass update attributes and import csv done.
     *
     * @param array $bunch
     * @param ProductInterface $productData
     * @return array
     * @throws NoSuchEntityException
     */
    public function createMassRequestData(array $bunch, ProductInterface $productData, int $storeId): array
    {
        $productType = $productData->getTypeId();
        $productAttribute = [];
        $productAttribute[self::FULFILL] = false;
        $productAttribute[self::PART_NUMBER] = $this->getPartNumber($productData);

        if(isset($bunch[self::DR_HS_CODE])){
            $productAttribute[self::HS_CODE] = !empty($bunch[self::DR_HS_CODE]) ? $bunch[self::DR_HS_CODE] : null;
        }else{
            if($productData->getDrHsCode() === "0"){
                $productAttribute[self::HS_CODE] = null;
            }else{
                $productAttribute[self::HS_CODE] = $productData->getDrHsCode();
            }       
        }

        $eccnCode = isset($bunch[self::DR_ECCN_CODE])
            ? $bunch[self::DR_ECCN_CODE]
            : $productData->getDrEccnCode();
        $productAttribute[self::ECCN] = $eccnCode; 
        
        $productAttribute[self::SKU_GROUP_ID] = $bunch[self::DR_SKU_GROUP_ID] ?? $productData->getDrSkuGroupId(); 

        $taxCode = isset($bunch[self::DR_TAX_CODE])
            ? $this->getIdByOptionLabel($bunch[self::DR_TAX_CODE], self::DR_TAX_CODE)
            : $productData->getDrTaxType();
        $productAttribute[self::TAX_CODE] = $taxCode;

        $name = isset($bunch[self::NAME])
            ? $bunch[self::NAME]
            : $productData->getName();
        $productAttribute[self::NAME] = $name;

        $countryOfOrigin = isset($bunch[self::DR_COUNTRY_OF_ORIGIN])
            ? $this->getIdByOptionLabel($bunch[self::DR_COUNTRY_OF_ORIGIN], self::DR_COUNTRY_OF_ORIGIN)
            : $productData->getDrCountryOfOrigin();
        $productAttribute[self::COUNTRY_OF_ORIGIN] = $countryOfOrigin;

        $dataCollection = $this->taxTypeCollection->create()->getData();
        foreach ($dataCollection as $item) {
            if ($item['entity_id'] === $taxCode) {
                $productAttribute[self::TAX_CODE] = $item['dr_sabrixcode'];
            }
        }
        $productData->setStoreId($storeId);
        $productAttribute["metadata"]["storeId"] = $storeId;

        return $productAttribute;
    }

    /**
     * Get PartNumber for the Payload Request
     * If product type is virtual or downloadable, partNumber will be null otherwise SKU
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getPartNumber(ProductInterface $product): ?string
    {
        $partNumber = '';
        if ($product->getTypeId() === DownloadableProduct::TYPE_DOWNLOADABLE ||
            $product->getTypeId() === ProductType::TYPE_VIRTUAL) {
            $productAttribute[self::PART_NUMBER] = '';
        } else {
            $partNumber = $product->getSku();
        }
        return $partNumber;
    }

    /**
     * Get Product attribute by code
     *
     * @param string $code
     * @return ProductAttributeInterface
     * @throws NoSuchEntityException
     */
    private function getAttribute(string $code): ProductAttributeInterface
    {
        if ($this->attribute === null) {
            $this->attribute = $this->attributeRepository->get($code);
        }

        return $this->attribute;
    }

    /**
     * Check If country of origin length should be greater than 2 otherwise
     * return as label.
     *
     * Get Product attribute country of origin
     *
     * @param string $label
     * @param string $code
     * @return string
     * @throws NoSuchEntityException
     */
    private function getIdByOptionLabel(string $label, string $code): string
    {
        if (strlen($label) > 2) {
            $attribute = $this->getAttribute($code);
            $label = $attribute->getSource()->getOptionId($label);
        }

        return $label;
    }

    /**
     * Returns product sku
     *
     * @param $productId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getSkuByID($productId): string
    {
        return $this->productRepository->getById($productId)->getSku();
    }
}