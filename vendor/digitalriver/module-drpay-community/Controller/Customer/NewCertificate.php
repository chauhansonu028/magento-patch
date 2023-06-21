<?php

namespace Digitalriver\DrPay\Controller\Customer;

use Digitalriver\DrPay\ViewModel\TaxManagement;
use Magento\Checkout\Model\Session;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Digitalriver\DrPay\Model\Customer\CustomerTaxCertificate;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Save new certificate
 */
class NewCertificate extends Action implements HttpPostActionInterface, AccountInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var CustomerTaxCertificate
     */
    private $certificateModel;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    private $logger;


    /** @var Session  */
    private $checkoutSession;

    /**
     * @param Validator $formKeyValidator
     * @param Context $context
     * @param CustomerTaxCertificate $certificateModel
     * @param ManagerInterface $messageManager
     * @param Session $checkoutSession
     */
    public function __construct(
        Validator $formKeyValidator,
        Context $context,
        CustomerTaxCertificate $certificateModel,
        ManagerInterface $messageManager,
        Session $checkoutSession,
        \Digitalriver\DrPay\Logger\Logger $logger
    ) {
        $this->validator = $formKeyValidator;
        $this->certificateModel = $certificateModel;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Save certificate data
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->validator->validate($this->getRequest()) && !$this->getRequest()->getPost()) {
            $this->messageManager->addErrorMessage(__('Invalid request. Please try again.'));
            return $this->redirectToCertificateList();
        }

        try {
            $response = $this->certificateModel->saveCustomerCertificate(
                $this->getRequest()->getParams(),
                $this->getRequest()->getFiles()
            );

            if (empty($response) || !$response['success']) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong, please contact site administrator')
                );
                return $this->redirectToCertificateList();
            }

            $quote = $this->checkoutSession->getQuote();
            if ($quote && $quote->getItemsCount()) {
                $this->checkoutSession->unsSessionCheckSum();
            }

            $this->messageManager->addSuccessMessage(
                __('Certificate uploaded successfully.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong, please contact site administrator.')
            );

            $this->logger->error(
                __('Digital River NewCertificate: An error occurred when saving the certificate file.'),
                [
                    'exception' => $e->getMessage()
                ]
            );
        }

        return $this->redirectToCertificateList();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function redirectToCertificateList()
    {
        return $this->_redirect('drpay/customer/index');
    }
}
