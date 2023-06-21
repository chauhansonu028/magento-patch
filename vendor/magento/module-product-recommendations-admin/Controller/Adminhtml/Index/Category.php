<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductRecommendationsAdmin\Controller\Adminhtml\Index;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Controller responsible for getting all category data for exclusions
 */
class Category extends AbstractAction
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    private $categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CollectionFactory $categoryCollection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $categoryCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryCollection;
        parent::__construct($context);
    }

    /**
     * Execute category controller call
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();
        $storeViewCode = $this->getRequest()->getParam('storeViewCode', '');
        $result = $this->getCategories($storeViewCode);
        return $jsonResult->setData($result);
    }

    /**
     * Get Categories by store view code
     *
     * @param string|null $storeViewCode
     * @return CategoryInterface[]
     * @throws LocalizedException
     */
    public function getCategories(?string $storeViewCode): array
    {
        $storeViewId = $storeViewCode ? $this->getStoreViewIdFromCode($storeViewCode) : null;

        $items = $this->categoryFactory->create();
        $items->addAttributeToSelect(['name', 'url_key', 'url_path']);
        $items->setStore($storeViewId);

        $categories = [];
        foreach ($items as $category) {
            $urlKey = $category->getUrlKey();
            if ($urlKey) {
                $categories[] = [
                    'name' => $category->getName(),
                    'urlKey' => $urlKey,
                    'urlPath' => $category->getUrlPath()
                ];
            }
        }
        return $categories;
    }

    /**
     * Get the store view id from the store view code
     *
     * @param string $storeViewCode
     * @return int|null
     */
    private function getStoreViewIdFromCode(string $storeViewCode): ?int
    {
        $stores = $this->storeManager->getStores(false, true);
        $storeViewId = null;
        if (isset($stores[$storeViewCode])) {
            $storeViewId = (int) $stores[$storeViewCode]->getId();
        }
        return $storeViewId;
    }

    /**
     * Check is user can access to Product Recommendations
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_ProductRecommendationsAdmin::product_recommendations');
    }
}
