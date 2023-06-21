<?php
/**
 *  Charge model repository
 *
 * @summary Provides model for charges
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\ChargeRepositoryInterface;
use Digitalriver\DrPay\Api\Data\ChargeInterface;
use Digitalriver\DrPay\Logger\Logger as DrLogger;
use Digitalriver\DrPay\Model\ResourceModel\Charge as ChargeResourceModel;
use Digitalriver\DrPay\Model\ResourceModel\Charge\CollectionFactory as CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class ChargeRepository to perform CRUD Operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ChargeRepository implements ChargeRepositoryInterface
{

    /**
     * @var ChargeResourceModel
     */
    protected $chargeResourceModel;

    /**
     * @var ChargeFactory
     */
    protected $chargeFactory;

    /**
     * @var CollectionFactory
     */
    protected $chargeCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DrLogger
     */
    private $drLogger;

    /**
     * ChargeRepository constructor.
     * @param ChargeResourceModel $chargeResourceModel
     * @param ChargeFactory $chargeFactory
     * @param CollectionFactory $chargeCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DrLogger $drLogger
     */
    public function __construct(
        ChargeResourceModel $chargeResourceModel,
        ChargeFactory $chargeFactory,
        CollectionFactory $chargeCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        DrLogger $drLogger
    ) {
        $this->chargeResourceModel = $chargeResourceModel;
        $this->chargeFactory = $chargeFactory;
        $this->chargeCollectionFactory = $chargeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->drLogger = $drLogger;
    }

    /**
     * Save Charge Record.
     *
     * @param ChargeInterface $charge
     *
     * @return void
     * @throws LocalizedException
     */
    public function save(ChargeInterface $charge): void
    {
        try {
            $this->chargeResourceModel->save($charge);
        } catch (Exception $exception) {
            $this->drLogger->critical("Charge saving error: ", [$exception->getMessage()]);
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * Load Charge data by given id
     *
     * @param int $chargeId
     *
     * @return ChargeInterface
     */
    public function getById(int $chargeId): ChargeInterface
    {
        $charge = $this->chargeFactory->create();
        $this->chargeResourceModel->load($charge, $chargeId);
        if (!$charge->getEntityId()) {
            $this->drLogger->alert("Charge with id $chargeId does not exist.", []);
        }
        return $charge;
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
        $collection = $this->chargeCollectionFactory->create();

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
     * @param ChargeInterface $charge
     *
     * @return bool
     */
    public function delete(ChargeInterface $charge): bool
    {
        try {
            $this->chargeResourceModel->delete($charge);
        } catch (Exception $exception) {
            $this->drLogger->alert("Charge delete error", [$exception->getMessage()]);
        }
        return true;
    }

    /**
     * Delete Catalog sync by given Identity
     *
     * @param int $chargeId
     *
     * @return bool
     */
    public function deleteById(int $chargeId): bool
    {
        return $this->delete($this->getById($chargeId));
    }

    /**
     * Saves a Charge
     *
     * @param string $drChargeId
     * @param int $orderId
     * @param string $drOrderId
     * @param string $drSourceId
     * @param string $drSourceType
     * @param float $amount
     */
    public function saveCharge(
        string $drChargeId,
        int $orderId,
        string $drOrderId,
        string $drSourceId,
        string $drSourceType,
        float $amount
    ): void {
        $charge = $this->chargeFactory->create();
        $charge->setDrChargeId($drChargeId);
        $charge->setOrderId($orderId);
        $charge->setDrOrderId($drOrderId);
        $charge->setDrSourceId($drSourceId);
        $charge->setDrSourceType($drSourceType);
        $charge->setAmount($amount);
        $charge->setRetrievedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        try {
            $this->save($charge);
        } catch (LocalizedException $exception) {
            $this->drLogger->error(sprintf('Charge Id %s Add/Update Sync Error', $drChargeId), [$exception]);
        }
    }
}
