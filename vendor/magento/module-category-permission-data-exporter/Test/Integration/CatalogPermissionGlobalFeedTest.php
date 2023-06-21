<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryPermissionDataExporter\Test\Integration;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogPermissions\Model\Permission;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\App\ReinitableConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Indexer\Model\Processor;

/**
 * Test the product override data feed
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogPermissionGlobalFeedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Feed indexer
     */
    private const FEED_INDEXER = 'catalog_data_exporter_category_permissions';

    /**
     * Feed name
     */
    private const FEED_NAME = 'categoryPermissions';

    /**
     * @var \Magento\DataExporter\Model\FeedInterface
     */
    private $feed;

    /**
     * @var Indexer|mixed
     */
    private $indexer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Permission|mixed
     */
    private $permission;

    /**
     * @var ReinitableConfig
     */
    private $config;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->feed = Bootstrap::getObjectManager()->get(FeedPool::class)->getFeed(self::FEED_NAME);
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->permission = $this->objectManager->create(Permission::class);
        $this->config = Bootstrap::getObjectManager()->get(ReinitableConfig::class);
        require_once 'Magento/Catalog/_files/categories.php';
    }

    protected function tearDown(): void
    {
        $this->permission->delete();
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        require 'Magento/Catalog/_files/categories_rollback.php';
        parent::tearDownAfterClass();
    }

    /**
     * @magentoConfigFixture base_website catalog/magento_catalogpermissions/grant_checkout_items 1
     * @magentoConfigFixture base_website catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture base_website catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/enabled 1
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGlobalEnableSpecificWebsite()
    {
        $this->runIndexer();
        $actual = $this->getFeedByIds(['ADD_TO_CART', 'DISPLAYED', 'DISPLAY_PRODUCT_PRICE', 'GLOBAL_ENABLED']);
        $expected = [
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'ADD_TO_CART',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'ADD_TO_CART' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'ADD_TO_CART',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'ADD_TO_CART' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAYED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAYED' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAYED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAYED' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'GLOBAL_ENABLED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'GLOBAL_ENABLED' => 'ALLOW',
                    ],
                'deleted' => false,
            ]
        ];

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_checkout_items 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_product_price 2
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_product_price_groups 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/enabled 1
     */
    public function testGlobalEnableSpecificGroup()
    {
        $this->runIndexer();
        $actual = $this->getFeedByIds(['DISPLAY_PRODUCT_PRICE']);
        $expected = [
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => '356a192b7913b04c54574d18c28d46e6395428ab',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
        ];

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_checkout_items 1
     * @magentoConfigFixture base_website catalog/magento_catalogpermissions/grant_checkout_items 0
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture base_website catalog/magento_catalogpermissions/grant_catalog_category_view 0
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @magentoConfigFixture base_website catalog/magento_catalogpermissions/grant_catalog_product_price 0
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/enabled 0
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGlobalDisableSpecificWebsite()
    {
        $this->runIndexer();
        $actual = $this->getFeedByIds(['ADD_TO_CART', 'DISPLAYED', 'DISPLAY_PRODUCT_PRICE', 'GLOBAL_ENABLED']);
        $expected = [
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'ADD_TO_CART',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'ADD_TO_CART' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'ADD_TO_CART',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'ADD_TO_CART' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAYED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAYED' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAYED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAYED' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'base',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'GLOBAL_ENABLED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'GLOBAL_ENABLED' => 'DENY',
                    ],
                'deleted' => false,
            ]
        ];

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_checkout_items 0
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_category_view 0
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_product_price 0
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/enabled 0
     */
    public function testGlobalDisableDefault()
    {
        $this->runIndexer();
        $actual = $this->getFeedByIds(['ADD_TO_CART', 'DISPLAYED', 'DISPLAY_PRODUCT_PRICE', 'GLOBAL_ENABLED']);
        $expected = [
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'ADD_TO_CART',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'ADD_TO_CART' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAYED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAYED' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'DENY',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'GLOBAL_ENABLED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'GLOBAL_ENABLED' => 'DENY',
                    ],
                'deleted' => false,
            ]
        ];

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_checkout_items 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @magentoConfigFixture default/catalog/magento_catalogpermissions/enabled 1
     */
    public function testGlobalEnableDefault()
    {
        $this->markTestSkipped('config value is not inherited for websites, this test framework issue');
        $this->runIndexer();
        $actual = $this->getFeedByIds(['ADD_TO_CART', 'DISPLAYED', 'DISPLAY_PRODUCT_PRICE', 'GLOBAL_ENABLED']);
        $expected = [
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'ADD_TO_CART',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'ADD_TO_CART' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAYED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAYED' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'DISPLAY_PRODUCT_PRICE',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'DISPLAY_PRODUCT_PRICE' => 'ALLOW',
                    ],
                'deleted' => false,
            ],
            [
                'id' =>
                    [
                        'websiteCode' => 'FALLBACK_WEBSITE',
                        'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                        'categoryId' => 'GLOBAL_ENABLED',
                    ],
                'type' => 'GLOBAL',
                'permission' =>
                    [
                        'GLOBAL_ENABLED' => 'ALLOW',
                    ],
                'deleted' => false,
            ]
        ];

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * If grand catalog category view is deny other grands will deny too without checking real status
     */
    public function testAllCustomerGroupWhenFirstDeny()
    {
        $this->permission->setWebsiteId(1)
            ->setCategoryId(6)
            ->setCustomerGroupId(null) //null it's all customer group
            ->setGrantCatalogCategoryView(Permission::PERMISSION_DENY)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_ALLOW)
            ->setGrantCheckoutItems(Permission::PERMISSION_PARENT)
            ->save();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                    'categoryId' => '6',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'DENY',
                    'DISPLAY_PRODUCT_PRICE' => 'DENY',
                    'ADD_TO_CART' => 'DENY',
                ],
            'deleted' => false
        ]];

        $this->runIndexer();
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function testAllCustomerGroupAllAllow()
    {
        $this->permission->setWebsiteId(1)
            ->setCategoryId(6)
            ->setCustomerGroupId(null) //null it's all customer group
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_ALLOW)
            ->setGrantCheckoutItems(Permission::PERMISSION_ALLOW)
            ->save();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                    'categoryId' => '6',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'ALLOW',
                    'DISPLAY_PRODUCT_PRICE' => 'ALLOW',
                    'ADD_TO_CART' => 'ALLOW',
                ],
            'deleted' => false
        ]];

        $this->runIndexer();
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function testAllCustomerGroupAllDeny()
    {
        $this->permission->setWebsiteId(1)
            ->setCategoryId(6)
            ->setCustomerGroupId(null) //null it's all customer group
            ->setGrantCatalogCategoryView(Permission::PERMISSION_DENY)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_DENY)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                    'categoryId' => '6',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'DENY',
                    'DISPLAY_PRODUCT_PRICE' => 'DENY',
                    'ADD_TO_CART' => 'DENY',
                ],
            'deleted' => false
        ]];

        $this->runIndexer();
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function testAllCustomerGroupParent()
    {
        $this->permission->setWebsiteId(1)
            ->setCategoryId(6)
            ->setCustomerGroupId(null) //null it's all customer group
            ->setGrantCatalogCategoryView(Permission::PERMISSION_PARENT)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_PARENT)
            ->save();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                    'categoryId' => '6',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'USE_PARENT',
                    'DISPLAY_PRODUCT_PRICE' => 'USE_PARENT',
                    'ADD_TO_CART' => 'USE_PARENT',
                ],
            'deleted' => false
        ]];

        $this->runIndexer();
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function testAllCustomerGroup()
    {
        $this->permission->setWebsiteId(1)
            ->setCategoryId(6)
            ->setCustomerGroupId(null) //null it's all customer group
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => 'FALLBACK_CUSTOMER_GROUP',
                    'categoryId' => '6',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'ALLOW',
                    'DISPLAY_PRODUCT_PRICE' => 'USE_PARENT',
                    'ADD_TO_CART' => 'DENY',
                ],
            'deleted' => false
        ]];

        $this->runIndexer();
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function testAllWebsite()
    {
        $this->permission->setWebsiteId(null) //null it's all website
            ->setCategoryId(6)
            ->setCustomerGroupId(1)
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'FALLBACK_WEBSITE',
                    'customerGroupCode' => '356a192b7913b04c54574d18c28d46e6395428ab',
                    'categoryId' => '6',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'ALLOW',
                    'DISPLAY_PRODUCT_PRICE' => 'USE_PARENT',
                    'ADD_TO_CART' => 'DENY',
                ],
            'deleted' => false
        ]];

        $this->runIndexer();
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function testDelete()
    {
        $this->permission->setWebsiteId(1)
        ->setCategoryId(12)
            ->setCustomerGroupId(1)
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();

        $this->runIndexer();

        $this->permission->delete();

        $this->runIndexer([$this->permission->getId()]);
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => '356a192b7913b04c54574d18c28d46e6395428ab',
                    'categoryId' => '12',
                ],
            'type' => 'STANDARD',
            'permission' => null,
            'deleted' => true
        ]];
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento_CategoryPermissionDataExporter::Test/_files/category_permissions_data_exporter_indexer_update_on_schedule.php
     *
     * @return void
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testDeleteCategory()
    {
        $categoryId = 12;
        $this->permission->setWebsiteId(1)
            ->setCategoryId($categoryId)
            ->setCustomerGroupId(1)
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();

        $this->runIndexer();

        $categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        try {
            $categoryRepository->delete($categoryRepository->get($categoryId));
        } catch (\Exception $e) {
            throw new \RuntimeException('Can\'t delete category: ' . $e->getMessage());
        }

        $processor = $this->objectManager->create(Processor::class);
        $processor->updateMview();
        $processor->reindexAllInvalid();
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => '356a192b7913b04c54574d18c28d46e6395428ab',
                    'categoryId' => $categoryId,
                ],
            'type' => 'STANDARD',
            'permission' => null,
            'deleted' => true
        ]];
        $actual = $this->getFeedByIds([$categoryId]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    public function _testChangeScope()
    {
        $this->permission->setWebsiteId(1)
            ->setCategoryId(12)
            ->setCustomerGroupId(1)
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();

        $this->runIndexer();

        $this->permission->setWebsiteId(1)
            ->setCategoryId(12)
            ->setCustomerGroupId(2)
            ->setGrantCatalogCategoryView(Permission::PERMISSION_ALLOW)
            ->setGrantCatalogProductPrice(Permission::PERMISSION_PARENT)
            ->setGrantCheckoutItems(Permission::PERMISSION_DENY)
            ->save();

        $this->permission->delete();

        $this->runIndexer([$this->permission->getId()]);
        $expected = [[
            'id' =>
                [
                    'websiteCode' => 'base',
                    'customerGroupCode' => '356a192b7913b04c54574d18c28d46e6395428ab',
                    'categoryId' => '12',
                ],
            'type' => 'STANDARD',
            'permission' =>
                [
                    'DISPLAYED' => 'ALLOW',
                    'DISPLAY_PRODUCT_PRICE' => 'USE_PARENT',
                    'ADD_TO_CART' => 'DENY',
                ],
            'deleted' => true
        ]];
        $actual = $this->getFeedByIds([6,12]);

        self::assertEquals($expected, $actual, 'Actual feed data doesn\'t equal to expected data');
    }

    /**
     * Returns orderFeeds by IDs
     *
     * @param array $ids
     * @return array
     * @throws \Zend_Db_Statement_Exception
     * @throws LocalizedException
     */
    private function getFeedByIds(array $ids): array
    {
        $output = [];
        $timestamp = new \DateTime('Now - 60 second');
        foreach ($this->feed->getFeedSince($timestamp->format(\DateTime::W3C))['feed'] as $item) {
            if (\in_array($item['id']['categoryId'], $ids)) {
                unset($item['_permission_id'], $item['_website_id'], $item['_customer_group_id'], $item['modifiedAt']);
                $output[] = $item;
            }
        }
        return $output;
    }

    /**
     * Run the indexer to extract feed data
     * @param array|null $ids permission ids
     * @return void
     */
    private function runIndexer(array $ids = null) : void
    {
        try {
            $this->indexer->load(self::FEED_INDEXER);
            $ids ? $this->indexer->reindexList($ids) : $this->indexer->reindexAll();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Could not reindex data');
        }
    }
}
