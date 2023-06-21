<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductOverrideDataExporter\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\DataExporter\Export\Processor;

/**
 * Test prices collection for product override data feed
 */
class ProductOverrideConfigurableProductPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = Bootstrap::getObjectManager()->create(Processor::class);
    }

    /**
     * Tests prices collection for configurable products
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/ConfigurableProductDataExporter/_files/setup_configurable_products.php
     */
    public function testConfigurableProductPrice()
    {
        $overrides = $this->processor->process('productOverrides', [['productId' => 40]]);
        foreach ($overrides as $override) {
            self::assertEquals('configurable1', $override['sku']);
            self::assertEquals(50.00, $override['prices']['minimumPrice']['regularPrice']);
            self::assertEquals(5.99, $override['prices']['minimumPrice']['finalPrice']);
            self::assertEquals(70.00, $override['prices']['maximumPrice']['regularPrice']);
            self::assertEquals(5.99, $override['prices']['maximumPrice']['finalPrice']);
        }
    }

}
