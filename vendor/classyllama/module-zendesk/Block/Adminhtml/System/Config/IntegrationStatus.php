<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Zendesk\Zendesk\Helper\Integration;
use Zendesk\Zendesk\Helper\ScopeHelper;
use Zendesk\Zendesk\Helper\Config as ConfigHelper;

class IntegrationStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * @var Integration
     */
    protected $integrationHelper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * IntegrationStatus constructor.
     * @param Context $context
     * @param ScopeHelper $scopeHelper
     * @param Integration $integrationHelper
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        // end parent parameters
        ScopeHelper                             $scopeHelper,
        Integration                             $integrationHelper,
        ConfigHelper                            $configHelper,
        // end custom parameters
        array                                   $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeHelper = $scopeHelper;
        $this->integrationHelper = $integrationHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     *
     * Set template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/integration-status.phtml');
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
        $isZendeskAppConfigured = $this->configHelper->isZendeskAppConfigured($scopeType, $scopeId);

        try {
            $integration = $this->integrationHelper->getIntegration($scopeType, $scopeId);

            $integrationMessage = __('Integration successfully configured on the current scope');
            $integrationActionUrl = null;
        } catch (NoSuchEntityException $e) {
            $integrationMessage = __('Integration not configured on the current scope');
            $integrationActionUrl = $this->getUrl('zendesk/system_config/fixIntegration', [$scopeType => $scopeId]);
        }

        $this->addData(
            [
                'action_url' => $integrationActionUrl,
                'button_label' => $integrationMessage,
                'html_id' => $element->getHtmlId(),
                'is_zendesk_app_configured' => $isZendeskAppConfigured
            ]
        );

        return $this->_toHtml();
    }
}
