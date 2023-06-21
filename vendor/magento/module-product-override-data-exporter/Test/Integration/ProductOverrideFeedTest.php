<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Indexer\Stock;
use Magento\CatalogPermissions\Model\Indexer\Product as IndexerProduct;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use RuntimeException;
use Throwable;

/**
 * Test the product override data feed
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ProductOverrideFeedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Product Override feed indexer
     */
    private const FEED_INDEXER = 'catalog_data_exporter_product_overrides';

    /**
     * @var \Magento\DataExporter\Model\FeedInterface
     */
    private $productOverrides;

    /**
     * @var Indexer|mixed
     */
    private $indexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GroupManagementInterface|mixed
     */
    private $customerGroupManagement;

    /**
     * @var Stock
     */
    private $stockIndexer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productOverrides = Bootstrap::getObjectManager()->get(FeedPool::class)->getFeed('productOverrides');
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->customerGroupManagement = Bootstrap::getObjectManager()->create(GroupManagementInterface::class);
        $this->stockIndexer = Bootstrap::getObjectManager()->create(Stock::class);
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Tests prices collection for configurable products
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testSimpleProduct()
    {
        $product = $this->productRepository->get('simple');
        $this->runIndexer();
        $productsFeed = $this->getProductOverrideFeedByIds([$product->getId()]);
        foreach ($productsFeed as $feed) {
            self::assertEquals("base", $feed['websiteCode']);
            self::assertEquals("simple", $feed['sku']);
            self::assertEquals(10, $feed['prices']['minimumPrice']['regularPrice']);
            self::assertEquals(10, $feed['prices']['minimumPrice']['finalPrice']);
            self::assertEquals(10, $feed['prices']['maximumPrice']['regularPrice']);
            self::assertEquals(10, $feed['prices']['maximumPrice']['finalPrice']);
            self::assertEquals(false, $feed['deleted']);
        }
    }

    /**
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 1
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/CatalogPermissions/_files/permission.php
     */
    public function testProductsWithPermissions()
    {
        $product = $this->productRepository->get('simple-4');
        $this->runIndexer(true);
        $productsFeed = $this->getProductOverrideFeedByIds([$product->getId()]);
        foreach ($productsFeed as $feed) {
            self::assertEquals("base", $feed['websiteCode']);
            self::assertEquals("simple-4", $feed['sku']);
            self::assertEquals(10, $feed['prices']['minimumPrice']['regularPrice']);
            self::assertEquals(10, $feed['prices']['minimumPrice']['finalPrice']);
            self::assertEquals(10, $feed['prices']['maximumPrice']['regularPrice']);
            self::assertEquals(10, $feed['prices']['maximumPrice']['finalPrice']);
            self::assertEquals(false, $feed['deleted']);
            self::assertEquals(true, $feed['displayable']);
            self::assertEquals(true, $feed['priceDisplayable']);
            self::assertEquals(true, $feed['addToCartAllowed']);
        }
    }

    /**
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 1
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/CatalogPermissions/_files/permission.php
     */
    public function testOutOfStockProductsWithPermissions()
    {
        $sku = 'simple-4';
        $product = $this->productRepository->get($sku);
        $this->runIndexer(true);
        $inStockProductsFeed = $this->getProductOverrideFeedByIds([$product->getId()]);
        foreach ($inStockProductsFeed as $feed) {
            self::assertEquals("base", $feed['websiteCode']);
            self::assertEquals($sku , $feed['sku']);
            self::assertEquals(10, $feed['prices']['minimumPrice']['regularPrice']);
            self::assertEquals(10, $feed['prices']['minimumPrice']['finalPrice']);
            self::assertEquals(10, $feed['prices']['maximumPrice']['regularPrice']);
            self::assertEquals(10, $feed['prices']['maximumPrice']['finalPrice']);
            self::assertEquals(false, $feed['deleted']);
            self::assertEquals(true, $feed['displayable']);
            self::assertEquals(true, $feed['priceDisplayable']);
            self::assertEquals(true, $feed['addToCartAllowed']);
        }

        $this->makeProductOutOfStock($sku);
        $productFeed = $this->getProductOverrideFeedByIds([$product->getId()]);
        self::assertEmpty($productFeed);
    }

    /**
     * @magentoConfigFixture default_store catalog/magento_catalogpermissions/enabled 0
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/CatalogPermissions/_files/permission.php
     */
    public function testWithCategoryPermissionsDisabled()
    {
        $product = $this->productRepository->get('simple-4');
        $productsFeed = $this->getProductOverrideFeedByIds([$product->getId()]);
        foreach ($productsFeed as $feed) {
            self::assertTrue(empty($feed['displayable']));
            self::assertTrue(empty($feed['priceDisplayable']));
            self::assertTrue(empty($feed['addToCartAllowed']));
        }
    }

    /**
     * Returns orderFeeds by IDs
     *
     * @param array $ids
     * @return array
     * @throws \Zend_Db_Statement_Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProductOverrideFeedByIds(array $ids): array
    {
        // TODO: currently made this test to work with one customer group only (default)
        $customerGroupIds = [
            sha1((string)$this->customerGroupManagement->getDefaultGroup()->getId())
        ];
        $output = [];
        foreach ($this->productOverrides->getFeedSince('1')['feed'] as $item) {
            if (!$item['deleted']
                && \in_array($item['productId'], $ids, false)
                    && \in_array($item['customerGroupCode'], $customerGroupIds, false)) {
                $output[] = $item;
            }
        }
        return $output;
    }

    /**
     * Run the indexer to extract products override data
     *
     * @param bool $reindexPermissions
     * @return void
     *
     */
    protected function runIndexer(bool $reindexPermissions = false) : void
    {
        try {
            $this->stockIndexer->executeFull();
            if ($reindexPermissions) {
                $this->indexer->load(IndexerProduct::INDEXER_ID);
                $this->indexer->reindexAll();
            }
            $this->indexer->load(self::FEED_INDEXER);
            $this->indexer->reindexAll();
        } catch (Throwable $e) {
            throw new RuntimeException('Could not reindex products override data');
        }
    }

    /**
     * @param string $sku
     * @return void
     * @throws NoSuchEntityException
     */
    private function makeProductOutOfStock(string $sku): void
    {
        /** @var StockRegistryInterface $stockRegistry */
        $stockRegistry = $this->objectManager->create(StockRegistryInterface::class);
        $stockItem = $stockRegistry->getStockItemBySku($sku);
        $stockItem->setUseConfigManageStock(true);
        $stockItem->setQty(0);
        $stockItem->setIsInStock(false);
        $stockRegistry->updateStockItemBySku($sku, $stockItem);
        $this->runIndexer();
    }
}
