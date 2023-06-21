<?php

namespace Digitalriver\DrPay\Controller\Customer;

use Magento\Customer\Controller\AccountInterface;

/**
 * Show new certificate page
 */
class AddNewCert extends \Magento\Framework\App\Action\Action implements AccountInterface
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
