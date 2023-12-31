<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $storeGroupRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param UrlInterface $backendUrl
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $storeGroupRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Context $context,
        // End parent parameters
        UrlInterface $backendUrl,
        StoreRepositoryInterface $storeRepository,
        GroupRepositoryInterface $storeGroupRepository,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        parent::__construct($context);
        $this->backendUrl = $backendUrl;
        $this->storeRepository = $storeRepository;
        $this->storeGroupRepository = $storeGroupRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Get backend landing redirect page URL
     *
     * @param array $params
     * @return string
     */
    protected function getBackendLandingUrl(array $params)
    {
        return $this->backendUrl->getUrl('zendesk/landing/index', $params);
    }

    /**
     * Given a customer ID, get backend URL which deep links to its detail page
     *
     * @param int $customerId
     * @return string
     */
    public function getCustomerDeepLinkUrl($customerId)
    {
        return $this->getBackendLandingUrl(['customer_id' => $customerId]);
    }

    /**
     * Given an order ID, get a backend URL which deep links to its detail page.
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderDeepLinkUrl($orderId)
    {
        return $this->getBackendLandingUrl(['order_id' => $orderId]);
    }

    /**
     * Get default store view of default store group of default website.
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultStore()
    {
        $defaultWebsite = $this->websiteRepository->getDefault();
        $defaultStoreGroup = $this->storeGroupRepository->get($defaultWebsite->getDefaultGroupId());
        $defaultStore = $this->storeRepository->getById($defaultStoreGroup->getDefaultStoreId());

        return $defaultStore;
    }

    /**
     * Get store ID of current store config request scope, or default if not applicable.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreConfigScopeStoreId(\Magento\Framework\App\RequestInterface $request)
    {
        // Get website corresponding to currently selected scope.
        $websiteId = $request->getParam('website');
        $website = $websiteId !== null ? $this->websiteRepository->getById($websiteId)
            : $this->websiteRepository->getDefault();

        // Get store corresponding to currently selected scope, or website's default store view if none selected.
        $storeId = $request->getParam(
            'store',
            // default to website's default store group's default store's ID
            $this->storeGroupRepository->get($website->getDefaultGroupId())->getDefaultStoreId()
        );

        return $storeId;
    }
}
