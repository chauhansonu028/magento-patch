<?php

namespace Digitalriver\DrPay\Controller\Payment;

use Digitalriver\DrPay\Model\DrOrderManagement;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Dr API success controller
 */
class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var DrOrder
     */
    private $drOrderManagement;

    /**
     * @param Context $context
     * @param DrOrderManagement $drOrderManagement
     */
    public function __construct(
        Context $context,
        DrOrderManagement $drOrderManagement
    ) {
        $this->drOrderManagement = $drOrderManagement;
        parent::__construct($context);
    }

    /**
     * Payment Success response
     *
     * @return mixed|null
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->drOrderManagement->placeOrder()) {
            return $result->setUrl($this->_url->getUrl('checkout/onepage/success'));
        }

        return $result->setUrl($this->_url->getUrl('checkout/cart'));
    }
}
