<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use Digitalriver\DrPay\Model\StoredMethods\ConfigProvider as StoredMethodsConfig;

/**
 * Dr API Savedrquote controller
 */
class Savedrquote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface
     */
    private $digitalRiverCustomerIdManagement;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var StoredMethodsConfig
     */
    private $storedMethodsConfig;

    /**
     * @var \Digitalriver\DrPay\Helper\Data
     */
    private $drHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface $digitalRiverCustomerIdManagement,
        \Magento\Checkout\Model\Session $checkoutSession,
        StoredMethodsConfig $storedMethodsConfig,
        \Digitalriver\DrPay\Helper\Data $drHelper
    ) {
        parent::__construct($context);
        $this->digitalRiverCustomerIdManagement = $digitalRiverCustomerIdManagement;
        $this->checkoutSession = $checkoutSession;
        $this->storedMethodsConfig = $storedMethodsConfig;
        $this->drHelper = $drHelper;
    }

    /**
     * @return mixed|null
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $responseContent = [
            'success' => false,
            'content' => __("Unable to process")
        ];

        // if there is a source already associated with the checkout, we need a new checkout
        if ($this->checkoutSession->getIsDrPrimarySourceAssociatedWithCheckout()) {
            $this->checkoutSession->unsDrSourceId();
            $this->checkoutSession->unsSessionCheckSum();
            $this->checkoutSession->unsDrCheckoutBillingChecksum();
            $this->checkoutSession->unsDrCheckoutItemChecksum();
            $this->drHelper->setCheckout($this->checkoutSession->getQuote());
        }

        $checkoutId = $this->checkoutSession->getData('dr_checkout_id');
        $paymentSessionId = $this->checkoutSession->getData('dr_payment_session_id');
        $drQuoteError = $this->checkoutSession->getData('dr_quote_error');

        if ($paymentSessionId && !$drQuoteError) {
            $hasCustomerId = (bool)$this->digitalRiverCustomerIdManagement->getSessionDigitalRiverCustomerId();
            $savePayment = $hasCustomerId && $this->storedMethodsConfig->isEnabled();
            $sellingEntity = $this->checkoutSession->getData('dr_selling_entity');
            $responseContent = [
                'success' => true,
                'content' => [
                    'paymentSessionId' => $paymentSessionId,
                    'sellingEntity' => $sellingEntity,
                    'savePayment' => $savePayment,
                    'checkoutId' => $checkoutId
                ]
            ];
        }

        $response->setData($responseContent);
        return $response;
    }
}
