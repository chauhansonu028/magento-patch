<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Zendesk\Zendesk\Helper\ScopeHelper;

class ZendeskApp extends \Magento\Backend\App\Action
{
    /**
     * ACL resource ID
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';

    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * ZendeskApp constructor.
     * @param Action\Context $context
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        Action\Context $context,
        //end parent parameters
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ScopeHelper $scopeHelper
    ) {
        parent::__construct($context);

        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->messageManger = $context->getMessageManager();
        $this->storeManager = $storeManager;
        $this->scopeHelper = $scopeHelper;
    }

    /**
     * Install / uninstall Zendesk app
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $install = (bool)$this->getRequest()->getParam('install');

        list($scopeType, $scopeId) = $this->scopeHelper->getScope();

        if ($install) {
            try {
                $this->zendeskAppHelper->installZendeskApp($scopeType, $scopeId);

                $this->messageManager->addSuccessMessage(__('Zendesk App Installed'));
            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage(__('Unable to install Zendesk App: "%1".', $ex->getMessage()));
            }
        } else {
            try {
                $this->zendeskAppHelper->removeZendeskApp($scopeType, $scopeId);

                $this->messageManager->addSuccessMessage(__('Zendesk App Uninstalled for the current scope'));
            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage(__('Unable to uninstall Zendesk App: "%1".', $ex->getMessage()));
            }
        }

        return $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
