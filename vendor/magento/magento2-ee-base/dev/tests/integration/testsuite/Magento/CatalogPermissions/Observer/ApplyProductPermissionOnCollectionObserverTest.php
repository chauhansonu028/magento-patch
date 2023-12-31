<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogPermissions\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Layer\Search as SearchLayer;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogPermissions\App\ConfigInterface as PermissionsConfig;
use Magento\CatalogPermissions\Model\Indexer\Category;
use Magento\CatalogPermissions\Model\Indexer\Product;
use Magento\CatalogPermissions\Model\Permission as PermissionModel;
use Magento\CatalogPermissions\Test\Fixture\Permission as PermissionFixture;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\TargetRule\Block\Catalog\Product\ProductList\Related as RelatedProductList;
use Magento\TestFramework\Fixture\Config as FixtureConfig;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\CatalogPermissions\Observer\ApplyProductPermissionOnCollectionObserverTest class.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class ApplyProductPermissionOnCollectionObserverTest extends \PHPUnit\Framework\TestCase
{
    private const PARTIAL_TERM_UNSUPPORTED_DISTRIBUTIONS = [
        'elasticsearch',
        'opensearch'
    ];

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var IndexerRegistry $indexerRegistry */
        $indexerRegistry = $objectManager->create(IndexerRegistry::class);
        $indexerRegistry->get(Category::INDEXER_ID)->reindexAll();
        $indexerRegistry->get(Product::INDEXER_ID)->reindexAll();
        $indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
        $this->collection = $objectManager->create(SearchLayer::class)->getProductCollection();
        $this->session = $objectManager->get(Session::class);
    }

    /**
     * Test search collection size.
     *
     * @param int $customerGroupId
     * @param string $query
     * @param int $expectedSize
     * @dataProvider searchCollectionSizeDataProvider
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled true
     * @magentoDataFixture Magento/CatalogPermissions/_files/category_products_deny.php
     */
    public function testSearchCollectionSize($customerGroupId, $query, $expectedSize)
    {
        $this->session->setCustomerGroupId($customerGroupId);
        $this->collection->addSearchFilter($query);
        $this->collection->setVisibility([3,4]);

        $this->assertEquals($expectedSize, count($this->collection->getItems()));
        $this->assertEquals($expectedSize, $this->collection->getSize());
    }

    /**
     * Test search collection size using partial term match.
     *
     * @param $customerGroupId
     * @param $query
     * @param $expectedSize
     * @dataProvider searchCollectionSizeUsingPartialTermDataProvider
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled true
     * @magentoDataFixture Magento/CatalogPermissions/_files/category_products_deny.php
     */
    public function testSearchCollectionSizeUsingPartialTerm($customerGroupId, $query, $expectedSize)
    {
        if ($this->isUnsupportedSearchEngine()) {
            $this->markTestSkipped('Magento currently does not support partial-term match with current Search Engine');
        }

        $this->session->setCustomerGroupId($customerGroupId);
        $this->collection->addSearchFilter($query);
        $this->collection->setVisibility([3,4]);

        $this->assertEquals($expectedSize, count($this->collection->getItems()));
        $this->assertEquals($expectedSize, $this->collection->getSize());
    }

    #[
        FixtureConfig(PermissionsConfig::XML_PATH_ENABLED, true, ScopeInterface::SCOPE_STORE),
        DataFixture(CategoryFixture::class, as: 'c1'),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$c1.id$',
                'grant_catalog_category_view' => PermissionModel::PERMISSION_ALLOW,
                'grant_catalog_product_price' => PermissionModel::PERMISSION_DENY,
                'grant_checkout_items' => PermissionModel::PERMISSION_DENY,
            ]
        ),
        DataFixture(ProductFixture::class, ['sku' => 'p1', 'category_ids' => ['$c1.id$']], 'p1'),
        DataFixture(CategoryFixture::class, as: 'c2'),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$c2.id$',
                'grant_catalog_category_view' => PermissionModel::PERMISSION_ALLOW,
                'grant_catalog_product_price' => PermissionModel::PERMISSION_ALLOW,
                'grant_checkout_items' => PermissionModel::PERMISSION_ALLOW,
            ]
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'p2', 'category_ids' => ['$c2.id$'], 'product_links' => ['$p1$']],
            'p2'
        ),
    ]
    public function testRelatedProductsWithDisabledPrice(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('p2');
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->register('product', $product);
        $relatedProductList = Bootstrap::getObjectManager()->create(RelatedProductList::class);
        $items = $relatedProductList->getItemCollection();
        self::assertCount(1, $items);
        $product = array_pop($items);
        self::assertEquals('p1', $product->getSku());
    }

    /**
     * Checks if the search engine is currently configured to use any version of Elasticsearch/OpenSearch
     *
     * @return bool
     */
    public function isUnsupportedSearchEngine(): bool
    {
        /** @var ScopeConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(ScopeConfigInterface::class);
        $searchEngine = $config->getValue('catalog/search/engine');

        foreach (self::PARTIAL_TERM_UNSUPPORTED_DISTRIBUTIONS as $unsupported) {
            if (strpos($searchEngine, $unsupported) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Data provider for testSearchCollectionSize method.
     *
     * @return array
     */
    public function searchCollectionSizeDataProvider()
    {
        return [
            [1, 'simple_deny_122', 0],
            [1, 'simple_allow_122', 1]
        ];
    }

    /**
     * Data provider for testSearchCollectionSizeUsingPartialWord method.
     *
     * @return array
     */
    public function searchCollectionSizeUsingPartialTermDataProvider()
    {
        return [
            [1, 'simple_', 1],
            [0, 'simple_', 0]
        ];
    }
}
