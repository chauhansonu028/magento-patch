<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface;
use Digitalriver\DrPay\Helper\Drapi;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

/**
 * Class OnStoredMethodsPageVisit
 * event: customer_account_stored_methods_page_visit
 */
class OnStoredMethodsPageVisit implements ObserverInterface
{
    /**
     * @var config
     */
    private $config;

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var DigitalRiverCustomerIdManagementInterface
     */
    private $riverCustomerIdManagement;
    /**
     * @var Drapi
     */
    private $drapi;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * OnStoredMethodsPageVisit constructor.
     *
     * @param Config $config
     * @param Session $customerSession
     * @param DigitalRiverCustomerIdManagementInterface $riverCustomerIdManagement
     * @param Drapi $drapi
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Config $config,
        Session $customerSession,
        DigitalRiverCustomerIdManagementInterface $riverCustomerIdManagement,
        Drapi $drapi,
        ManagerInterface $messageManager
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->riverCustomerIdManagement = $riverCustomerIdManagement;
        $this->drapi = $drapi;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->getIsEnabled()) {
            return;
        }
        
        if (!$this->customerSession->isLoggedIn()) {
            return;
        }
        $customer = $this->customerSession->getCustomer();
        $drCustomerId = $this->riverCustomerIdManagement->hasDigitalRiverCustomerId(
            $customer->getDataModel()
        );
        if (!$drCustomerId) {
            try {
                $this->drapi->createDrId($customer->getDataModel());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error occurred while retrieving data. Try reload page.'));
            }
        }
    }
}
