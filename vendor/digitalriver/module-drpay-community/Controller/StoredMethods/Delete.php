<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\StoredMethods;

use Digitalriver\DrPay\Helper\Drapi;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class Delete implements HttpPostActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;
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
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        Drapi $drapi
    ) {
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->drapi = $drapi;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if ($sourceId = $this->request->getParam('source_id')) {
            $response = $this->drapi->deleteCustomerSource($sourceId);
            if ($response === null) {
                $this->messageManager->addSuccessMessage(__('Method successfully deleted'));
            }
        }
        return $this->redirectFactory->create()->setPath('drpay/storedmethods/index');
    }
}
