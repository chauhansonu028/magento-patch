<?php
/**
 * Validate Digital River Allocated Percent Field Observer
 *
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Observer;

use Digitalriver\DrPay\Model\BundleValidation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class ValidateAllocatedPercentFieldObserver implements ObserverInterface
{

    /**
     * @var BundleValidation
     */
    private $bundleValidation;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * ValidateAllocatedPercentFieldObserver constructor.
     * @param BundleValidation $bundleValidation
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(
        BundleValidation $bundleValidation,
        JsonSerializer $jsonSerializer
    ) {
        $this->bundleValidation = $bundleValidation;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param EventObserver $observer
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        $messages = $this->bundleValidation->validate($product);
        if (is_array($messages)) {
            foreach ($messages as $message) {
                throw new \Exception($this->jsonSerializer->serialize($message));
            }
        }
    }
}
