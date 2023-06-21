<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Data;
use Zendesk\Zendesk\Helper\ScopeHelper;

class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
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
     * TestConnection constructor.
     * @param Context $context
     * @param Api $apiHelper
     * @param Data $helper
     * @param ScopeHelper $scopeHelper
     * @param array $data
     */
    public function __construct(
        Context     $context,
        // end parent parameters
        Api         $apiHelper,
        Data        $helper,
        ScopeHelper $scopeHelper,
        // end custom parameters
        array       $data = []
    ) {
        parent::__construct($context, $data);
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
        $this->setTemplate('Zendesk_Zendesk::system/config/testconnection.phtml');
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
            // zendesk integration must not be configured.
            $zendeskConfigured = false;
        }

        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('zendesk/system_config/testconnection'),
                'zendesk_configured' => $zendeskConfigured
            ]
        );

        return $this->_toHtml();
    }
}
