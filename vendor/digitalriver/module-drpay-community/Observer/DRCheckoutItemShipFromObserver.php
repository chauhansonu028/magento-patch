<?php
/**
 * Sample observer for declaring custom shipFrom addresses for specific products
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class DRCheckoutItemShipFromObserver implements ObserverInterface
{

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getDataByKey('quote');

        $items = $quote->getDrItemsPayload();

        foreach ($items as $itemKey => $item) {
            if ($item['skuId'] === '24-MB03') {
                $items[$itemKey]['shipFrom']['address'] = [
                    'line1' => '1265 Lombardi Ave.',
                    'line2' => '',
                    'city' => 'Green Bay',
                    'country' =>'US',
                    'state' => 'WI',
                    'postalCode' => '54301'
                ];
            }
        }

        $quote->setDrItemsPayload($items);
    }
}
