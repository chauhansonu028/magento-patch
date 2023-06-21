<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ScopeHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param Context $context
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        Context $context,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        parent::__construct($context);
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get scope_type and scope_id from request
     *
     * @return array
     */
    public function getScope()
    {
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = null;

        $websiteId = $this->_request->getParam('website');
        $storeId = $this->_request->getParam('store_id');
        $store = $this->_request->getParam('store');
        if ($websiteId !== null) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = (int)$websiteId;
        } elseif ($storeId !== null || $store !== null) {
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeId = (int)($storeId ?: $store);
        }
        return [$scopeType, $scopeId];
    }
}
