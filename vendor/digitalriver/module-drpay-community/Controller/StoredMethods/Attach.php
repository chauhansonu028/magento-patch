<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\StoredMethods;

use Digitalriver\DrPay\Helper\Drapi;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

class Attach implements HttpPostActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Drapi
     */
    private $drapi;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager,
        Drapi $drapi
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->drapi = $drapi;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $responseContent = [];
        if ($sourceId = $this->request->getParam('source_id')) {
            $response = $this->drapi->setCustomerSource($sourceId);
            if ($response['message'] !== null) {
                $responseContent = ['success' => true];
                $this->messageManager->addSuccessMessage(__('Method successfully saved'));
            } else {
                $responseContent = ['error' => true];
            }
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($responseContent);
    }
}
