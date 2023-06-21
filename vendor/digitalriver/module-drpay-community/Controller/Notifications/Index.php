<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Controller\Notifications;

use Digitalriver\DrPay\Model\DrConnectorFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Sales\Model\Order as Order;

/**
 * Hybrid notifications controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var DrConnectorFactory
     */
    private $drFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        Order $order,
        DrConnectorFactory $drFactory,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->drFactory = $drFactory;
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
        $content = $this->getRequest()->getContent();
        $data = $this->jsonSerializer->unserialize($content);
        $responseContent = [];
        if (is_array($data) && isset($data['OrderEventNotification'])) {
            $OrderEventNotification = $data['OrderEventNotification'];
            $responseContent = $this->processNotification($OrderEventNotification);
        } else {
            $responseContent = ["error" => "Invalid Request"];
        }
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData($responseContent);
        return $response;
    }

    public function processNotification($OrderEventNotification)
    {
        $eventType = '';
        $drOrderId = '';
        $eventType = isset($OrderEventNotification["event"]["id"]) ? $OrderEventNotification["event"]["id"] : '';
        $drOrderId = isset($OrderEventNotification["order"]["id"]) ? $OrderEventNotification["order"]["id"] : '';
        $model = $this->drFactory->create()->load($drOrderId, 'requisition_id');
        if ($model && $model->getId()) {
            $model->setPostStatus(1)->save();
        }
        $order = $this->order->load($drOrderId, 'dr_order_id');
        if ($order && $order->getId()) {
            if ($order->isPaymentReview()) {
                $order->setState(Order::STATUS_FRAUD)->setStatus(Order::STATUS_FRAUD)->save();
            }
            if ($order->canCancel()) {
                //cancel order
                $order->cancel();
                $description = isset($OrderEventNotification["event"]["description"]) ?
                    $OrderEventNotification["event"]["description"] : 'Order canceled';
                $comment = $eventType .' : '. $description;
                $order->addStatusToHistory(
                    $order->getStatus(),
                    __($comment)
                );
                $order->save();
            }
        }
    }
}
