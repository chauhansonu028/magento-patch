<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Zendesk\Zendesk\Helper\Config;

class WebWidgetCustomize extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * WebWidgetCustomize constructor.
     * @param Context $context
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        // end parent required parameters
        Config $configHelper,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
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
        $this->setTemplate('Zendesk_Zendesk::system/config/web-widget-customize-link.phtml');
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
     * Get web-widget customization URL
     *
     * @return string
     *
     * Get dynamic web widget customization URL
     */
    public function getWebWidgetCustomizationUrl()
    {
        $domain = $this->configHelper->getSubDomain();

        if (empty($domain)) {
            return ''; // Cannot compute customization URL without domain
        }
        // add .zendesk.com to the end of the url.
        $domain = rtrim($domain, '/') . ".zendesk.com";

        return str_replace(
            Config::DOMAIN_PLACEHOLDER,
            $domain,
            $this->configHelper->getWebWidgetCustomizeUrlPattern()
        );
    }

    /**
     * @inheritdoc
     *
     * Get element output
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
