<?php
namespace Zendesk\Zendesk\Controller\Adminhtml\Landing;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Index extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @inheritdoc
     *
     * @return Redirect
     */
    public function execute()
    {
        $request = $this->getRequest();
        $redirect = $this->redirectFactory->create();
        try {
            if ($orderId = $request->getParam('order_id')) {
                $redirect->setPath('sales/order/view', ['order_id' => $orderId]);
            } elseif ($orderIncrementId = $this->getRequest()->getParam('order_increment_id')) {
                $order = $this->getOrderByIncrementId($orderIncrementId);
                $redirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            } elseif ($customerId = $this->getRequest()->getParam('customer_id')) {
                $redirect->setPath('customer/index/edit', ['id' => $customerId]);
            } else {
                throw new \InvalidArgumentException(__('Invalid input parameter'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something was wrong during processing your request. Try again')
            );
            $redirect->setPath($this->_backendUrl->getStartupPageUrl());
        }
        return $redirect;
    }

    /**
     * Get order by increment_id
     *
     * @param string $incrementId
     * @return OrderInterface|mixed
     * @throws NoSuchEntityException
     */
    protected function getOrderByIncrementId($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $order = reset($orderList);
        if (!$order) {
            throw new NoSuchEntityException(__('Invalid order increment ID'));
        }
        return reset($orderList);
    }

    /**
     * Process URL keys
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        if ($this->_auth->isLoggedIn()) {
            return true;
        }
        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        $this->_redirect($this->_backendUrl->getStartupPageUrl());
        return false;
    }
}
