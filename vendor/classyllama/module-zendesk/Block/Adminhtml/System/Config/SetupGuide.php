<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

class SetupGuide extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @inheritdoc
     *
     * Set template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/setup-guide.phtml');
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
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }
}
