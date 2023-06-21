<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Queue;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;

class QueueProcessor
{
    public const RULESPRO_CONDITION_STRING = '%Amasty\\\\\\\\RulesPro\\\\\\\\Model\\\\\\\\Rule\\\\\\\\Condition%';

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CollectionFactory
     */
    private $salesruleCollectionFactory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        QuoteRepository $quoteRepository,
        CollectionFactory $salesruleCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        QuoteFactory $quoteFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->salesruleCollectionFactory = $salesruleCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Call rule validation to cache result for RulesPro conditions.
     */
    public function process(array $customerIds): void
    {
        $rules = $this->getRulesWithRulesProConditions();

        foreach ($customerIds as $customerId) {
            try {
                $quote = $this->quoteRepository->getActiveForCustomer($customerId);
            } catch (\Exception $exception) {
                //there is no active quote for this customer
                $customer = $this->customerRepository->getById($customerId);
                $quote = $this->quoteFactory->create();
                $quote->assignCustomer($customer);
            }

            foreach ($rules as $rule) {
                $rule->validate($quote);
            }
        }
    }

    /**
     * @return Rule[]
     */
    public function getRulesWithRulesProConditions(): array
    {
        $salesruleCollection = $this->salesruleCollectionFactory->create();
        $salesruleCollection->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('conditions_serialized', ['like' => self::RULESPRO_CONDITION_STRING]);

        return $salesruleCollection->getItems();
    }
}
