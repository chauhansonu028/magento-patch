<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SearchScopeResolver
 *
 * Resolves website, store and storeview
 */
class SearchScopeResolver
{
    /**
     * @var ScopeResolverInterface
     */
    private ScopeResolverInterface $scopeResolver;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Get store view code
     *
     * @return string
     */
    public function getStoreViewCode(): string
    {
        return $this->scopeResolver->getScope()->getCode();
    }

    /**
     * Get store code
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        $storeViewId = $this->scopeResolver->getScope()->getId();
        $storeId = $this->storeManager->getStore($storeViewId)->getStoreGroupId();
        return $this->storeManager->getGroup($storeId)->getCode();
    }

    /**
     * Get website code
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getWebsiteCode(): string
    {
        $storeViewId = $this->scopeResolver->getScope()->getId();
        $websiteId = $this->storeManager->getStore($storeViewId)->getWebsiteId();
        return $this->storeManager->getWebsite($websiteId)->getCode();
    }
}
