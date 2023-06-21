<?php

namespace Digitalriver\DrPay\Controller\Customer;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;

/**
 * Show Tax certificate page
 */
class Index extends Action implements AccountInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
