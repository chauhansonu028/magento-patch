<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Zendesk\Zendesk\Helper\ScopeHelper;
use Zendesk\Zendesk\Helper\ZendeskApp as ZendeskAppHelper;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Data;

class ZendeskApp extends \Magento\Config\Block\System\Config\Form\Field
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
        Context $context,
        // end parent parameters
        ZendeskAppHelper                        $zendeskAppHelper,
        Api                                     $apiHelper,
        Data                                    $helper,
        ScopeHelper                             $scopeHelper,
        // end custom parameters
        array                                   $data = []
    ) {
        parent::__construct($context, $data);
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->apiHelper = $apiHelper;
        $this->scopeHelper = $scopeHelper;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     *
     * Set template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/zendesk-app.phtml');
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
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        list($scopeType, $scopeId) = $this->scopeHelper->getScope();

        try {
            $this->apiHelper->tryAuthenticate($scopeType, $scopeId);
            $zendeskConfigured = true;
        } catch (\Exception $ex) {
            // Zendesk must not yet be configured to successfully authenticate.
            $zendeskConfigured = false;
        }

        $isInstalled = $this->zendeskAppHelper->isZendeskAppInstalled($scopeType, $scopeId);
        $urlParams = ['install' => (int)!$isInstalled];
        if ($scopeType != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $urlParams[$scopeType] = $scopeId;
        }

        $this->addData(
            [
                'installed' => $isInstalled,
                'button_label' => $isInstalled
                    ? __('Uninstall Zendesk App')
                    : __('Install Zendesk App'),
                'html_id' => $element->getHtmlId(),
                'install_url' => $this->_urlBuilder->getUrl('zendesk/system_config/zendeskApp', $urlParams),
                'zendesk_configured' => $zendeskConfigured
            ]
        );

        return $this->_toHtml();
    }
}
