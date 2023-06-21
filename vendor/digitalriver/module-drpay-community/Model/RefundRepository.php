<?php
/**
 *  Refund model repository
 *
 * @summary Provides model for refunds
 * @author V
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\RefundRepositoryInterface;
use Digitalriver\DrPay\Api\Data\RefundInterface;
use Digitalriver\DrPay\Logger\Logger as DrLogger;
use Digitalriver\DrPay\Model\ResourceModel\Refund as RefundResourceModel;
use Digitalriver\DrPay\Model\ResourceModel\Refund\CollectionFactory as CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RefundRepository to perform CRUD Operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class RefundRepository implements RefundRepositoryInterface
{

    /**
     * @var RefundResourceModel
     */
    protected $refundResourceModel;

    /**
     * @var RefundFactory
     */
    protected $refundFactory;

    /**
     * @var CollectionFactory
     */
    protected $refundCollectionFactory;

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
     * RefundRepository constructor.
     * @param RefundResourceModel $refundResourceModel
     * @param RefundFactory $refundFactory
     * @param CollectionFactory $refundCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DrLogger $drLogger
     */
    public function __construct(
        RefundResourceModel $refundResourceModel,
        RefundFactory $refundFactory,
        CollectionFactory $refundCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        DrLogger $drLogger
    ) {
        $this->refundResourceModel = $refundResourceModel;
        $this->refundFactory = $refundFactory;
        $this->refundCollectionFactory = $refundCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->drLogger = $drLogger;
    }

    /**
     * Save Refund Record.
     *
     * @param RefundInterface $refund
     *
     * @return void
     * @throws LocalizedException
     */
    public function save(RefundInterface $refund): void
    {
        try {
            $this->refundResourceModel->save($refund);
        } catch (Exception $exception) {
            $this->drLogger->critical("Refund saving error: ", [$exception->getMessage()]);
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * Load Refund data by given id
     *
     * @param string $drRefundId
     *
     * @return RefundInterface
     */
    public function getById(string $drRefundId): RefundInterface
    {
        $refund = $this->refundFactory->create();
        $this->refundResourceModel->load($refund, $drRefundId);
        if (!$refund->getDrRefundId()) {
            $this->drLogger->alert("Refund with id $drRefundId does not exist.", []);
        }
        return $refund;
    }

    /**
     * Find Refunds by SearchCriteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultsInterface
    {
        $collection = $this->refundCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var SearchResultsInterfaceFactory $searchResult */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Refund record
     *
     * @param RefundInterface $refund
     *
     * @return bool
     */
    public function delete(RefundInterface $refund): bool
    {
        try {
            $this->refundResourceModel->delete($refund);
        } catch (Exception $exception) {
            $this->drLogger->alert("REfund delete error", [$exception->getMessage()]);
        }
        return true;
    }

    /**
     * Delete Refund by given Id
     *
     * @param string $drRefundId
     *
     * @return bool
     */
    public function deleteById(string $drRefundId): bool
    {
        return $this->delete($this->getByDrRefundId($drRefundId));
    }

    /**
     * Saves a Refund
     *
     * @param string $drRefundId
     * @param float $amount
     */
    public function saveRefund(
        string $drRefundId,
        float $amount
    ): void {
        $refund = $this->refundFactory->create();
        $refund->setId($drRefundId);
        $refund->setAmount($amount);
        try {
            $this->save($refund);
        } catch (LocalizedException $exception) {
            $this->drLogger->error(sprintf('Refund Id %s Add/Update Sync Error', $drRefundId), [$exception]);
        }
    }
}
