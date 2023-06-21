<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use Magento\Directory\Helper\Data as magentoData;
use Magento\Directory\Model\Country as country;

/**
 * Dr API Savedrsource controller
 */
class Savedrsource extends \Magento\Framework\App\Action\Action
{
    /**
     * Constant defined for DR API field value
     */
    public const CREDIT_CARD = 'creditCard';
    public const PAYMENT_METHOD_DROPIN_CODE = 'drpay_dropin';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param \Digitalriver\DrPay\Logger\Logger     $logger
     * @param \Digitalriver\DrPay\Helper\Data       $helper
     * @param \Digitalriver\DrPay\Helper\Config     $config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Digitalriver\DrPay\Helper\Data $helper,
        \Digitalriver\DrPay\Helper\Config $config,
        magentoData $magentoData
    ) {
        $this->helper =  $helper;
        $this->config = $config;
        $this->quoteResource = $quoteResource;
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
        $this->magentoData = $magentoData;
        parent::__construct($context);
    }

    /**
     * Trenslates payment info values and title before it is sent to dropin template.
     * @param $paymentInfo
     * @return array
     */
    protected function translatePaymentInfo($paymentInfo) : array
    {
        $translatedPayment = [];

        if ($paymentInfo) {
            $counter = 0;
            $translatedPayment = ['title' => __('Payment Information')];
            foreach ($paymentInfo as $key => $value) {
                if ($value === self::CREDIT_CARD) { // progress further
                    $counter = ++$counter;
                } elseif ($counter === 0) { // applepay, googlepay, translate, set and exit for
                    $translatedPaymentInfo = __($value);
                } else { // cc, progress with string construction
                    if ($counter === 1) { // this is cc brand, concatenate and set
                        $translatedPaymentInfo = __($value) . ' ' . __('ending in') . ' ';
                        $counter = ++$counter;
                    } elseif ($counter === 2) { // this is last 4 digits, concatenate, exit for
                        $translatedPaymentInfo .= $value;
                    }
                }
            }
            $translatedPayment += ['payment_info' => $translatedPaymentInfo];
        }
        return $translatedPayment;
    }

    /**
     * @return mixed|null
     */
    public function execute()
    {
        $responseContent = [
            'success'        => false,
            'content'        => __("Unable to process")
        ];

        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $isEnabled = $this->config->getIsEnabled();
        if (!$isEnabled) {
            return $response->setData($responseContent);
        }

        $sourceId = $this->getRequest()->getParam('sourceId');
        $this->_checkoutSession->setDrSourceId($sourceId);
        $this->_checkoutSession->unsDrReadyForStorage();
        $readyForStorage = $this->getRequest()->getParam('readyForStorage');
        if (!empty($readyForStorage) && $readyForStorage == "true") {
            $this->_checkoutSession->setDrReadyForStorage("store");
        }

        $sourceInfo = $this->helper->getCheckoutSourceInfo($sourceId);
        $paymentInfo = $sourceInfo['payment'];
        $quote = $this->_checkoutSession->getQuote();
        $sourceState = !empty($sourceInfo['message']['owner']['address']['state'])?$sourceInfo['message']['owner']['address']['state']:null;
        $drSourceBillingAddress = $this->helper->getBillingAddressFromSource($sourceInfo);
        if (!$drSourceBillingAddress) {
            return $response->setData([
                'success'        => false,
                'content'        => __("Invalid billing address, please try again.")
            ]);
        }

        if ($quote && $quote->getId() && $quote->getIsActive() && $drSourceBillingAddress) {

            $billingAddress = $quote->getBillingAddress();
            
            // if a saved address is used, we need to remove the addressId if the DR source changes the address
            // if this is not done, the address will revert to the saved address upon order submission
            if($billingAddress->getCustomerAddressId() > 0) {

                // account for line1 & line2
                $billingStreet = $billingAddress->getStreet();
                $billingStreetForComparison = $billingStreet !== false ? implode("\n", $billingStreet) : '';

                $currentBillingAddress = [
                    "firstname" => $billingAddress->getFirstname(),
                    "lastname" => $billingAddress->getLastName(),
                    "street" => $billingStreetForComparison,
                    "city" => $billingAddress->getCity(),
                    "postcode" => $billingAddress->getPostcode(),
                    "country_id" => $billingAddress->getCountryId(),
                    "region" => $billingAddress->getRegionCode() ?? $billingAddress->getRegion(),
                    "region_id" => $billingAddress->getRegionId(),
                    "telephone" => $billingAddress->getTelephone()
                ];

                if(empty($currentBillingAddress['region'])){
                    unset($currentBillingAddress['region']);
                }

                if(empty($currentBillingAddress['region_id'])){
                    unset($currentBillingAddress['region_id']);
                }

                if($drSourceBillingAddress != $currentBillingAddress) {
                    $billingAddress->setCustomerAddressId(0);
                }
            }

            $allowedRegions = $this->magentoData->getRegionData();
            $configdata = $allowedRegions['config']['regions_required'];
            if (array_search($drSourceBillingAddress['country_id'], $configdata) !== false) {
                if (($drSourceBillingAddress['region']&&$drSourceBillingAddress['region_id'])) {
                    $regionId = $allowedRegions[$drSourceBillingAddress['country_id']][$drSourceBillingAddress['region_id']];
                    $this->_logger->info("region id code ".$regionId["code"]);
                    $this->_logger->info("sourceState ".$sourceState);
                    if ($regionId && $regionId["code"] == $sourceState) {
                        $this->setUpdatedBillingAddress($drSourceBillingAddress);
                    } else {
                        return $response->setData([
                            'success'        => false,
                            'content'        => __("Invalid billing address, please enter valid state code and try again.")
                        ]);
                    } 
                } elseif (empty($drSourceBillingAddress['region'])&&empty($drSourceBillingAddress['region_id'])) {
                    if ($billingAddress->getCountryId() === $drSourceBillingAddress['country_id']) {
                        $drSourceBillingAddress = array_filter($drSourceBillingAddress);
                        $billingAddress->addData($drSourceBillingAddress);
                    }
                } else {
                    return $response->setData([
                        'success'        => false,
                        'content'        => __("Invalid billing address, please enter valid state code and try again.")
                    ]);
                }
            } else {
                $this->setUpdatedBillingAddress($drSourceBillingAddress);
            }

            //MAGENTO-164 included state name field to update in frontend
            if ($sourceInfo &&
                $sourceInfo['message'] &&
                $sourceInfo['message']['owner'] &&
                $sourceInfo['message']['owner']['address']
            ) {
                $sourceInfo['message']['owner']['address']['stateName'] = $quote->getBillingAddress()->getRegion();
            }
            $payment = $quote->getPayment();
            if ($payment) {
                if ($paymentInfo) {
                    if ($paymentInfo['type'] == 'creditCard') {
                        $payment->setCcType($paymentInfo['brand']);
                        $payment->setCcLast4($paymentInfo['lastFourDigits']);
                    }
                    $payment->setMethod(self::PAYMENT_METHOD_DROPIN_CODE);
                }
            }
            $quote->collectTotals();
            $this->quoteResource->save($quote);
        }

        $checkoutSourceInfo = [];
        $checkoutSourceInfo['message']['owner'] = $sourceInfo['message']['owner'];

        $responseContent = [
            'success' => true,
            'content' => $this->translatePaymentInfo($paymentInfo),
            'sourceInfo' => $checkoutSourceInfo
        ];

        $checkoutId = $this->_checkoutSession->getDrCheckoutId();
        $this->_checkoutSession->setDrLockedInCheckoutId($checkoutId);

        $response->setData($responseContent);
        return $response;
    }

    public function setUpdatedBillingAddress($drSourceBillingAddress):void{
        $quote = $this->_checkoutSession->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setFirstname($drSourceBillingAddress['firstname']);
        $billingAddress->setLastName($drSourceBillingAddress['lastname']);
        $billingAddress->setStreet($drSourceBillingAddress['street']);
        $billingAddress->setCity($drSourceBillingAddress['city']);
        $billingAddress->setPostcode($drSourceBillingAddress['postcode']);
        $billingAddress->setCountryId($drSourceBillingAddress['country_id']);
        $billingAddress->setRegionCode($drSourceBillingAddress['region']);
        $billingAddress->setRegion($drSourceBillingAddress['region']);
        $billingAddress->setRegionId($drSourceBillingAddress['region_id']);
        $billingAddress->setTelephone($drSourceBillingAddress['telephone']);
    }
}