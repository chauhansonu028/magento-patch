<?php
/**
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Info render block
 */
class Info extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field|null
     */
    protected $fieldRenderer;

    /**
     * Render fieldset html
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $html .= $this->getSysInfo($element);
        $html .= $this->_getFooterHtml($element);
        return $html;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function getFieldRenderer()
    {
        if (empty($this->fieldRenderer)) {
            $this->fieldRenderer = $this->_layout->createBlock(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }

        return $this->fieldRenderer;
    }
    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getSysInfo($fieldset)
    {
        $label = __("Connector Version:");
        return $this->getFieldHtml($fieldset, 'sys_info', $label, "3.1.3");
    }

    /**
     * @param AbstractElement $fieldset
     * @param string          $fieldName
     * @param string          $label
     * @param string          $value
     *
     * @return string
     */
    protected function getFieldHtml($fieldset, $fieldName, $label = '', $value = '')
    {
        $field = $fieldset->addField(
            $fieldName,
            'label',
            [
                'name'  => 'dummy',
                'label' => $label,
                'after_element_html' => $value,
            ]
        )->setRenderer($this->getFieldRenderer());

        return $field->toHtml();
    }
}
