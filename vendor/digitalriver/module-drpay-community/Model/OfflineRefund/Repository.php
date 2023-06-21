<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Model\OfflineRefund;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundSearchResultInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundSearchResultInterfaceFactory;
use Digitalriver\DrPay\Api\OfflineRefundRepositoryInterface;
use Digitalriver\DrPay\Api\Data\OfflineRefundInterfaceFactory;
use Digitalriver\DrPay\Model\Data\OfflineRefund;
use Digitalriver\DrPay\Model\ResourceModel\OfflineRefund as OfflineRefundResourceModel;
use Digitalriver\DrPay\Model\ResourceModel\OfflineRefund\CollectionFactory;
use Exception;
use Laminas\Hydrator\HydratorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class OfflineOfflineRefundRepository to perform CRUD Operation
 */
class Repository implements OfflineRefundRepositoryInterface
{
    /**
     * @var OfflineRefundResourceModel
     */
    private $offlineRefundResourceModel;

    /**
     * @var OfflineRefundInterfaceFactory
     */
    private $offlineRefundFactory;

    /**
     * @var CollectionFactory
     */
    private $offlineRefundCollectionFactory;

    /**
     * @var HydratorInterface
     */
    private $offlineRefundHydrator;

    /**
     * @var OfflineRefundSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var LoggerInterface
     */
    private $drLogger;

    /**
     * OfflineRefundRepository constructor.
     * @param OfflineRefundResourceModel $offlineRefundResourceModel
     * @param OfflineRefundInterfaceFactory $offlineRefundFactory
     * @param CollectionFactory $offlineRefundCollectionFactory
     * @param HydratorInterface $offlineRefundHydrator
     * @param OfflineRefundSearchResultInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param LoggerInterface $drLogger
     */
    public function __construct(
        OfflineRefundResourceModel $offlineRefundResourceModel,
        OfflineRefundInterfaceFactory $offlineRefundFactory,
        CollectionFactory $offlineRefundCollectionFactory,
        HydratorInterface $offlineRefundHydrator,
        OfflineRefundSearchResultInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        LoggerInterface $drLogger
    ) {
        $this->offlineRefundResourceModel = $offlineRefundResourceModel;
        $this->offlineRefundFactory = $offlineRefundFactory;
        $this->offlineRefundCollectionFactory = $offlineRefundCollectionFactory;
        $this->offlineRefundHydrator = $offlineRefundHydrator;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->drLogger = $drLogger;
    }

    /**
     * Save OfflineRefund Record.
     *
     * @param OfflineRefundInterface $offlineRefund
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(OfflineRefundInterface $offlineRefund): OfflineRefundInterface
    {
        try {
            $offlineRefund = $this->getDataModel($offlineRefund);
            $this->offlineRefundResourceModel->save($offlineRefund);
            return $offlineRefund;
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()), $exception);
        } catch (Exception $exception) {
            $uid = uniqid('dr_', true);
            $this->drLogger->error(
                $exception->getMessage(),
                [
                    'uid' => $uid,
                    'stack_trace' => $exception->getTraceAsString()
                ]
            );

            throw new CouldNotSaveException(
                __('OfflineRefund model could not be saved. Error code: %1', $uid),
                $exception,
                $uid
            );
        }
    }

    /**
     * Load OfflineRefund data by given id
     *
     * @param string $drOfflineRefundId
     *
     * @return OfflineRefundInterface
     * @throws NoSuchEntityException
     */
    public function get(string $drOfflineRefundId): OfflineRefundInterface
    {
        /** @var OfflineRefund $offlineRefund */
        $offlineRefund = $this->offlineRefundFactory->create();
        $this->offlineRefundResourceModel->load(
            $offlineRefund,
            $drOfflineRefundId,
            OfflineRefundInterface::FIELD_DR_REFUND_ID
        );

        if (!$offlineRefund->getId()) {
            throw NoSuchEntityException::singleField(OfflineRefundInterface::FIELD_DR_REFUND_ID, $drOfflineRefundId);
        }

        return $offlineRefund;
    }

    /**
     * Load OfflineRefund data by given id
     *
     * @param string $drOfflineRefundId
     *
     * @return bool
     */
    public function checkExistence(string $drOfflineRefundId): bool
    {
        /** @var OfflineRefund $offlineRefund */
        $offlineRefund = $this->offlineRefundFactory->create();
        $this->offlineRefundResourceModel->load(
            $offlineRefund,
            $drOfflineRefundId,
            OfflineRefundInterface::FIELD_DR_REFUND_ID
        );

        if (!$offlineRefund->getId()) {
            return false;
        }

        return true;
    }

    /**
     * Find OfflineRefunds by SearchCriteria
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return OfflineRefundSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria): OfflineRefundSearchResultInterface
    {
        $collection = $this->offlineRefundCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var OfflineRefundSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->count());

        return $searchResults;
    }

    /**
     * Delete OfflineRefund record
     *
     * @param OfflineRefundInterface $offlineRefund
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(OfflineRefundInterface $offlineRefund): void
    {
        try {
            $this->offlineRefundResourceModel->delete($this->getDataModel($offlineRefund));
        } catch (LocalizedException $exception) {
            throw new CouldNotDeleteException(__($exception->getRawMessage(), $exception->getParameters()), $exception);
        } catch (Exception $exception) {
            $uid = uniqid('dr_', true);
            $this->drLogger->error(
                $exception->getMessage(),
                [
                    'uid' => $uid,
                    'stack_trace' => $exception->getTraceAsString()
                ]
            );

            throw new CouldNotDeleteException(
                __('OfflineRefund model could not be deleted. Error code: %1', $uid),
                $exception,
                $uid
            );
        }
    }

    /**
     * Get by creditmemo ID
     *
     * @param int $creditmemoId
     * @return OfflineRefundInterface
     * @throws NoSuchEntityException
     */
    public function getByCreditmemoId(int $creditmemoId): OfflineRefundInterface
    {
        /** @var OfflineRefund $offlineRefund */
        $offlineRefund = $this->offlineRefundFactory->create();
        $this->offlineRefundResourceModel->load(
            $offlineRefund,
            $creditmemoId,
            OfflineRefundInterface::FIELD_CREDIT_MEMO_ID
        );
        if (!$offlineRefund->getDrRefundId()) {
            throw NoSuchEntityException::singleField(OfflineRefundInterface::FIELD_CREDIT_MEMO_ID, $creditmemoId);
        }

        return $offlineRefund;
    }

    /**
     * Makes sure we have the proper implementation of the data model to pass to the resource model
     *
     * @param OfflineRefundInterface $model
     * @return OfflineRefund
     */
    private function getDataModel(OfflineRefundInterface $model): OfflineRefund
    {
        if ($model instanceof OfflineRefund) {
            return $model;
        }
        /** @var OfflineRefund $newModel */
        $newModel = $this->offlineRefundFactory->create();

        $this->offlineRefundHydrator->hydrate($this->offlineRefundHydrator->extract($model), $newModel);

        return $newModel;
    }
}
