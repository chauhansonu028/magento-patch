<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Ui\DataProvider;

use Digitalriver\DrPay\Helper\Drapi;
use Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Digitalriver\DrPay\Logger\Logger;

class StoredMethods extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var Drapi
     */
    private $drapi;
    /**
     * @var DigitalRiverCustomerIdManagementInterface
     */
    private $riverCustomerIdManagement;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        Drapi $drapi,
        DigitalRiverCustomerIdManagementInterface $riverCustomerIdManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        Logger $logger,
        array $meta = [],
        array $data = []        
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->drapi = $drapi;
        $this->riverCustomerIdManagement = $riverCustomerIdManagement;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->_logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $result = ['items' => []];
        $currentStoreId = $this->storeManager->getStore()->getId();
        $this->customerSession->setCurrentCustomerStoreId($currentStoreId);
        if ($drCustomerId = $this->riverCustomerIdManagement->getSessionDigitalRiverCustomerId()) {
            $response = $this->drapi->getCustomer($drCustomerId);
            $items = [];
            if (is_array($response)) {
                $customer = $response['message'];
                foreach ($customer['sources'] ?? [] as $source) {
                    if (isset($source['type']) && $source['reusable'] === true && $source['type'] == 'creditCard') {
                        $brand = $source['creditCard']['brand'];
                        if (preg_match('~[a-z]~', $brand)) {
                            $brand = \preg_replace('~([A-Z])~', ' ${1}', $brand);
                        }

                        $item = [
                            'source_id' => $source['id'],
                            'card_type' => $brand,
                            'expiration_date' => sprintf(
                                '%s/%s',
                                $source['creditCard']['expirationMonth'],
                                $source['creditCard']['expirationYear']
                            ),
                            'card_number' => __("ending %1", $source['creditCard']['lastFourDigits'])
                        ];
                        $items[] = $item;
                    }
                }
            }

            $result['items'] = $items;
        }

        return $result;
    }
}
