<?php declare(strict_types=1);

/**
 * Order Repository Plugin
 *
 * This plugin add duty_fee and IOR Tax attribute to the extension attribute order API
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Plugin;

use Digitalriver\DrPay\Helper\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

/**
 * Class Order Repository Get list.
 */
class OrderRepository
{
    private const DR_DUTY_FEE = 'dr_duty_fee';
    private const DR_IOR_TAX = 'dr_ior_tax';

    /**
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        OrderExtensionFactory $extensionFactory,
        Config $config
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->config = $config;
    }

    /**
     * Intercepts order to add 'DR_DUTY_FEE' extension attribute
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface           $order
     *
     * @return OrderInterface
     *
     * $subject parameter is not used in the plugin body.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): OrderInterface {
        $this->setDrAttribute($order);

        return $order;
    }

    /**
     * Adds DR_DUTY_FEE and DR_IOR_TAX extension attribute to order data object
     * to make it accessible in V1/orders/{id} API data
     *
     * @param OrderRepositoryInterface   $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     *
     * $subject parameter is not used in the plugin body.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ): OrderSearchResultInterface {
        $orders = $searchResult->getItems();
        foreach ($orders as $order) {
            $this->setDrAttribute($order);
        }

        return $searchResult;
    }

    /**
     * Adds DR_DUTY_FEE and  DR_IOR_TAX extension attribute to the order
     *
     * @param OrderInterface $order
     */
    private function setDrAttribute(OrderInterface $order): void
    {
        $drDutyFee = $order->getData(self::DR_DUTY_FEE);
        $drIorTax = $order->getData(self::DR_IOR_TAX);
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ?? $this->extensionFactory->create();
        $extensionAttributes->setDrDutyFee($drDutyFee);
        $extensionAttributes->setBaseDrDutyFee($this->config->convertToBaseCurrency($drDutyFee));
        $extensionAttributes->setDrIorTax($drIorTax);
        $extensionAttributes->setBaseDrIorTax($this->config->convertToBaseCurrency($drIorTax));
        $order->setExtensionAttributes($extensionAttributes);
    }
}
