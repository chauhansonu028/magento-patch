<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Controller\Unsubscribe;

use Amasty\EmailUnsubscribe\Model\Unsubscribe as UnsubscribeModel;
use Amasty\EmailUnsubscribe\Model\UrlHash;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class Index implements ActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var UnsubscribeModel
     */
    private $unsubscribe;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlHash
     */
    private $urlHash;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        UnsubscribeModel $unsubscribe,
        UrlHash $urlHash,
        MessageManagerInterface $messageManager
    ) {
        $this->resultFactory = $resultFactory;
        $this->unsubscribe = $unsubscribe;
        $this->request = $request;
        $this->urlHash = $urlHash;
        $this->messageManager = $messageManager;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $type = $this->request->getParam('type');
        $email = $this->request->getParam('email');
        $entityId = (int) $this->request->getParam('entity_id');
        $hash =  $this->request->getParam('hash');

        if ($type && $email && $this->urlHash->validate($type, $email, $hash)) {
            $redirectPath = $this->unsubscribe->execute($type, $email, $entityId);
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong.')
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath($redirectPath ?? '');
    }
}
