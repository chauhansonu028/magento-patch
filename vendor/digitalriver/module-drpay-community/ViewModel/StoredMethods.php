<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\ViewModel;

use Digitalriver\DrPay\Helper\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class StoredMethods implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Config $config,
        Session $session
    ) {
        $this->config = $config;
        $this->customerSession = $session;
    }

    /**
     * @return string
     */
    public function getDrKey(): string
    {
        return (string)$this->config->getPublicKey();
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return str_replace('_', '-', (string)$this->config->getLocale());
    }

    /**
     * @return string
     */
    public function getDefaultSellingEntity(): string
    {
        return (string)$this->config->getDefaultSellingEntity();
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * @return bool
     */
    public function getIsDrEnabled(): bool
    {
        return (bool)$this->config->getIsEnabled();
    }
}
