<?php
/**
 * Provides DR quote error details in checkout page
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\Quoteerror;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CheckoutSession
     */
    private $session;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $resultJsonFactory
     * @param CheckoutSession $session
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        CheckoutSession $session
    ) {
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        return parent::__construct($context);
    }

    /**
     * Returns Dr quote error in checkout page
     *
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();
        $options []  =
            [
                'dr_quote_error' => $this->session->getDrQuoteError(),
            ];
        return $result->setData($options);
    }
}
