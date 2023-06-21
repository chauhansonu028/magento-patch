<?php
/**
 * Receive all event data from the DR API webhooks
 */
namespace Digitalriver\DrPay\Controller\Receive;

use Digitalriver\DrPay\Logger\Logger;
use Digitalriver\DrPay\Model\DrConnectorRepository;
use Digitalriver\DrPay\Model\DrConnectorRepositoryFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Dr API Receive controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var DrConnectorRepositoryFactory
     */
    private $drConnectorRepositoryFactory;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        DrConnectorRepositoryFactory $drConnectorRepositoryFactory,
        Logger $logger,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->drConnectorRepositoryFactory = $drConnectorRepositoryFactory;
        $this->_logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
    }
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }

    public function execute()
    {
        $responseContent = ['success' => false, 'statusCode' => 200];
        $data = $this->getRequest()->getContent();
        $payload = $this->jsonSerializer->unserialize($data);
        if (in_array($payload['type'], DrConnectorRepository::AVAILABLE_WEBHOOKS)) {
            $type = strtoupper($payload['type']);
            $this->_logger->critical("\n\nWEBHOOK TYPE - $type\n\n");
            $this->_logger->critical("WEBHOOK PAYLOAD " . $data);
            $responseContent = $this->drConnectorRepositoryFactory->Create()->saveEventRequest($data);
            $this->_logger->info("WEBHOOK RESPONSE " . $this->jsonSerializer->serialize($responseContent));
        }

        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData($responseContent);
        $response->setHttpResponseCode($responseContent['statusCode']);
        return $response;
    }
}
