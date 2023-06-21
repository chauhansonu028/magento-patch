<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Controller\Fulfillment;

use Digitalriver\DrPay\Api\DrConnectorRepositoryInterface;
use Digitalriver\DrPay\Model\DrConnectorRepositoryFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Hybrid fulfillment controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var DrConnectorRepositoryInterface
     */
    private $drConnectorRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        DrConnectorRepositoryInterface $drConnectorRepository,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->drConnectorRepository = $drConnectorRepository;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getContent();
        $data = $this->jsonSerializer->unserialize(utf8_encode($data));
        if (is_array($data) && isset($data['OrderLevelElectronicFulfillmentRequest'])) {
            $orderLevelElectronicFulfillmentRequest = $data['OrderLevelElectronicFulfillmentRequest'];
            $responseContent = $this->drConnectorRepository->saveFulFillment($orderLevelElectronicFulfillmentRequest);
        } else {
            $responseContent = ["error" => "Invalid Request"];
        }
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData($responseContent);
        return $response;
    }
}
