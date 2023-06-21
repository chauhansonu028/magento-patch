<?php
/**
 *
 */
namespace Digitalriver\DrPay\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Getcards
 */
class Getsavedsources extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Digitalriver\DrPay\Helper\Data
     */
    private $helper;

    /**
     * @var \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface
     */
    private $digitalRiverCustomerIdManagement;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Digitalriver\DrPay\Helper\Data $helper,
        \Digitalriver\DrPay\Api\DigitalRiverCustomerIdManagementInterface $digitalRiverCustomerIdManagement
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->digitalRiverCustomerIdManagement = $digitalRiverCustomerIdManagement;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $responseContent = [
            'success'    => false,
            'content'    => ''
        ];

        $drCustomerId = $this->digitalRiverCustomerIdManagement->getSessionDigitalRiverCustomerId();

        if ($drCustomerId) {
            $result = $this->helper->getSavedSources($drCustomerId);
            if (isset($result['success'])) {
                $responseContent = [
                    'success' => $result['success'],
                    'content' => $result['message']
                ];
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($responseContent);
    }
}
