<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Config as ConfigHelper;
use Zendesk\Zendesk\Helper\Integration;
use Zendesk\Zendesk\Helper\ZendeskApp as ZendeskAppHelper;
use Zendesk\Zendesk\Helper\ScopeHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RemoveIntegration extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var Integration
     */
    protected $integrationHelper;

    /**
     * @var ZendeskAppHelper
     */
    protected $zendeskAppHelper;

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * RemoveIntegration constructor.
     * @param Context $context
     * @param Integration $integrationHelper
     * @param ZendeskAppHelper $zendeskAppHelper
     * @param Api $apiHelper
     * @param ScopeHelper $scopeHelper
     * @param ConfigHelper $configHelper
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        // end parent parameters
        Integration                             $integrationHelper,
        ZendeskAppHelper                        $zendeskAppHelper,
        Api                                     $apiHelper,
        ScopeHelper                             $scopeHelper,
        ConfigHelper                            $configHelper,
        StoreRepositoryInterface                $storeRepository,
        WebsiteRepositoryInterface              $websiteRepository,
        // end custom parameters
        array                                   $data = []
    ) {
        parent::__construct($context, $data);
        $this->integrationHelper = $integrationHelper;
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->apiHelper = $apiHelper;
        $this->scopeHelper = $scopeHelper;
        $this->configHelper = $configHelper;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @inheritdoc
     *
     * Set template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/remove-integration.phtml');
        return $this;
    }

    /**
     * @inheritdoc
     *
     * Unset irrelevant element data
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @inheritdoc
     *
     * Get element output
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        list($scopeType, $scopeId) = $this->scopeHelper->getScope();

        $urlParams = [];
        $isDefaultScope = $scopeType == ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if (!$isDefaultScope) {
            $urlParams[$scopeType] = $scopeId;
        }

        $this->addData(
            [
                'button_label' => __('Remove Zendesk Integration'),
                'html_id' => $element->getHtmlId(),
                'remove_url' => $this->_urlBuilder->getUrl('zendesk/system_config/removeIntegration', $urlParams),
                'scope_name' => $isDefaultScope ? $scopeType : $scopeType . " (ID: $scopeId)",
                'is_default_scope' => $isDefaultScope
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Get if Zendesk API configured at given scope, glossing over exception
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    protected function isApiConfiguredAtScope(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        try {
            $this->apiHelper->tryValidateIsConfigured($scopeType, $scopeCode);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Determine if button should or shouldn't display
     *
     * @return bool
     */
    public function displayButton()
    {
        list($scopeType, $scopeId) = $this->scopeHelper->getScope();
        // Check if integration present
        try {
            $this->integrationHelper->getIntegration($scopeType, $scopeId);

            $shouldDisplay = true;
        } catch (NoSuchEntityException $e) {
            $shouldDisplay = false;
        }

        // Done!
        return $shouldDisplay;
    }
}
