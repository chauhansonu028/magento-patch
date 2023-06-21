<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\Zendesk\Helper\Integration;
use Zendesk\Zendesk\Helper\ScopeHelper;

class FixIntegration extends \Magento\Backend\App\Action
{
    /**
     * ACL resource ID
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';

    /**
     * @var ManagerInterface
     */
    protected $messageManger;

    /**
     * @var Integration
     */
    protected $integrationHelper;

    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * FixIntegration constructor.
     * @param Action\Context $context
     * @param Integration $integrationHelper
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param ScopeHelper $scopeHelper
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Action\Context                     $context,
        //end parent parameters
        Integration                        $integrationHelper,
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        ScopeHelper                        $scopeHelper,
        StoreRepositoryInterface           $storeRepository,
        WebsiteRepositoryInterface         $websiteRepository
    ) {
        parent::__construct($context);

        $this->messageManger = $context->getMessageManager();
        $this->integrationHelper = $integrationHelper;
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->scopeHelper = $scopeHelper;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * If installed at given scope, remove and reinstall Zendesk app
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    protected function reinstallZendeskApp(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        if ($this->zendeskAppHelper->isZendeskAppInstalled($scopeType, $scopeId)) {
            $this->zendeskAppHelper->removeZendeskApp($scopeType, $scopeId);
            $this->zendeskAppHelper->installZendeskApp($scopeType, $scopeId);
        }
    }

    /**
     * @inheritdoc
     *
     * Ensure Zendesk integration is created
     */
    public function execute()
    {
        try {
            list($scopeType, $scopeId) = $this->scopeHelper->getScope();
            $this->integrationHelper->removeIntegration($scopeType, $scopeId); // clean up any existing integration
            $this->integrationHelper->createIntegration($scopeType, $scopeId); // create new, activated integration

            $this->reinstallZendeskApp($scopeType, $scopeId); // reinstall at default scope

            $this->messageManager->addSuccessMessage(__('Zendesk integration fixed for the current scope'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to fix Zendesk integration for the current scope'));
        }

        return $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
