<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Data;
use Zendesk\Zendesk\Helper\ZendeskApp as ZendeskAppHelper;
use Zendesk\Zendesk\Helper\ScopeHelper;

class ZendeskAppStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var ZendeskAppHelper
     */
    protected $zendeskAppHelper;

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * ZendeskApp constructor.
     * @param Context $context
     * @param ZendeskAppHelper $zendeskAppHelper
     * @param Api $apiHelper
     * @param Data $helper
     * @param ScopeHelper $scopeHelper
     * @param array $data
     */
    public function __construct(
        Context          $context,
        // end parent parameters
        ZendeskAppHelper $zendeskAppHelper,
        Api              $apiHelper,
        Data             $helper,
        ScopeHelper      $scopeHelper,
        // end custom parameters
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
        $this->scopeHelper = $scopeHelper;
    }

    /**
     * @inheritdoc
     *
     * Set template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/zendesk-app-status.phtml');
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

        try {
            $this->apiHelper->tryValidateIsConfigured($scopeType, $scopeId);
            $zendeskConfigured = true;
        } catch (\Exception $ex) {
            // Zendesk must not yet be configured to successfully authenticate.
            $zendeskConfigured = false;
        }

        try {
            $this->apiHelper->tryAuthenticate($scopeType, $scopeId);
            $authenticationConfirmed = true;
        } catch (\Exception $ex) {
            // Zendesk must not yet be configured to successfully authenticate.
            $authenticationConfirmed = false;
        }

        $statusMessage = __('Status unknown.');
        $isInstalled = false; // default value

        if (!$zendeskConfigured) {
            $statusMessage = __('Zendesk API credentials not configured');
        } elseif (!$authenticationConfirmed) {
            $statusMessage = __('Invalid API credentials.');
        } else {
            $isInstalled = $this->zendeskAppHelper->isZendeskAppInstalled($scopeType, $scopeId);

            $statusMessage = $isInstalled ?
                __('Successfully Installed')
                : __('Not Installed');
        }

        $this->addData(
            [
                'zendesk_configured' => $zendeskConfigured,
                'authentication_confirmed' => $authenticationConfirmed,
                'is_installed' => $isInstalled,
                'button_label' => $statusMessage,
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }
}
