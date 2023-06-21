<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\SetofFixed;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class SetofFixedTest
 *
 * @see SetofFixed
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class SetofFixedTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    public const ITEMS_COUNT = 10;
    public const RULE_DISCOUNT_STEP = 2;
    public const RULE_SIMPLE_ACTION = '';
    public const RULE_DISCOUNT_QTY = 0;
    public const RULE_DISCOUNT_AMOUNT = 50;
    /**#@-*/

    protected function setUp(): void
    {
        $this->initQuote();
    }

    /**
     * @covers SetofFixed::calculateDiscountForItems
     *
     * @throws \ReflectionException
     * @TODO: broken test, fix it
     */
    public function testCalculateDiscountForItems(): void
    {
        $total = $this->prepareQuoteItems(false);

        $dataFactory = $this->initDiscountDataFactory();
        $itemPrice = $this->initItemPrice();
        $priceCurrency = $this->initPriceCurrency();
        $rule = $this->initRule(false);

        /** @var MockObject|\Amasty\Rules\Model\RuleResolver $ruleResolver */
        $ruleResolver = $this->createPartialMock(\Amasty\Rules\Model\RuleResolver::class, ['getLinkId']);
        $ruleResolver->expects($this->any())->method('getLinkId')->will($this->returnValue(1));

        /** @var SetofFixed $action */
        $action = $this->getObjectManager()->getObject(
            SetofFixed::class,
            [
                'discountDataFactory' => $dataFactory,
                'itemPrice' => $itemPrice,
                'priceCurrency' => $priceCurrency,
                'ruleResolver' => $ruleResolver
            ]
        );

        $actualTotal = 0;

        $this->invokeMethod($action, 'calculateDiscountForItems', [$total, $rule, $this->items, static::RULE_DISCOUNT_AMOUNT]);

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discount */
        foreach (current($action::$cachedDiscount) as $discount) {
            $actualTotal += $discount->getBaseAmount();
        }

        $this->assertEquals(static::RULE_DISCOUNT_AMOUNT, ($total - $actualTotal));
    }

    /**
     * @covers SetofFixed::getBaseItemsPrice
     *
     * @throws \ReflectionException
     */
    public function testGetBaseItemsPrice(): void
    {
        $expectedTotal = $this->prepareQuoteItems(false);

        $action = $this->getMockBuilder(SetofFixed::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseItemsPrice'])
            ->getMock();
        $this->setProperty($action, 'validator', $this->initValidator());

        $actualTotal = $this->invokeMethod($action, 'getBaseItemsPrice', [$this->items]);

        $this->assertEquals($expectedTotal, $actualTotal);
    }
}
