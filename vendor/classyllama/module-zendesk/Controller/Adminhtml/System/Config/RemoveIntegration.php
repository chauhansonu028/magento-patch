<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zendesk\Zendesk\Helper\ScopeHelper;

class RemoveIntegration extends \Magento\Backend\App\Action
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
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Zendesk\Zendesk\Helper\WebWidget
     */
    protected $webWidgetHelper;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * RemoveIntegration constructor.
     * @param Action\Context $context
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        Action\Context $context,
        //end parent parameters
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        ScopeHelper $scopeHelper
    ) {
        parent::__construct($context);

        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->messageManger = $context->getMessageManager();
        $this->integrationHelper = $integrationHelper;
        $this->storeRepository = $storeRepository;
        $this->webWidgetHelper = $webWidgetHelper;
        $this->websiteRepository = $websiteRepository;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->scopeHelper = $scopeHelper;
    }

    /**
     * @inheritdoc
     *
     * Install / uninstall Zendesk app
     */
    public function execute()
    {
        list($scopeType, $scopeId) = $this->scopeHelper->getScope();

        try {
            // Remove Magento Integration
            $this->integrationHelper->removeIntegration($scopeType, $scopeId);
            $this->messageManager->addSuccessMessage(__('Zendesk integration successfully removed.'));
        } catch (\Exception $e) {
            $this->messageManger->addErrorMessage(__(
                'Zendesk integration not removed: %1',
                $e->getMessage()
            ));
        }

        // Remove Zendesk App
        if ($scopeType == ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            try {
                $this->zendeskAppHelper->removeZendeskApp($scopeType, $scopeId);

                $this->messageManager->addSuccessMessage(__('Zendesk App successfully removed for the current scope'));
            } catch (\Exception $e) {
                $this->messageManger->addErrorMessage(__(
                    'Zendesk App not removed: %1',
                    $e->getMessage()
                ));
            }
        }

        // Remove web widget
        $this->webWidgetHelper->toggleWebWidget(false, $scopeType, $scopeId);

        $this->messageManager->addSuccessMessage(__('Zendesk Web Widget disabled for the current scope'));

        // Clear API credentials
        foreach (\Zendesk\Zendesk\Helper\Config::API_CREDENTIAL_PATHS as $configPath) {
            $this->configWriter->delete($configPath, $scopeType, $scopeId);
        }
        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
        $this->messageManager->addSuccessMessage(__('Zendesk API credentials cleared for the current scope'));

        // Done!

        return $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
