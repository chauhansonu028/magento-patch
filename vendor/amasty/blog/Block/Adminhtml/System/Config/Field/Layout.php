<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Adminhtml\System\Config\Field;

use Amasty\Blog\Block\Adminhtml\System\Config\Field\Layout\Renderer;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Data\Layout as LayoutHelper;
use Amasty\Blog\Model\Source\Layout as LayoutOptions;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;

class Layout extends Field
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var LayoutHelper
     */
    private $helperLayout;

    /**
     * @var string
     */
    private $layout;

    /**
     * @var LayoutOptions
     */
    protected $layoutOptions;

    public function __construct(
        Context $context,
        Data $helperData,
        LayoutHelper $helperLayout,
        LayoutOptions $layoutOptions,
        string $layout = '',
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperData = $helperData;
        $this->helperLayout = $helperLayout;
        $this->layout = $layout;
        $this->layoutOptions = $layoutOptions;
    }

    protected function getContentBlocks(): array
    {
        $result = $this->helperLayout->getBlocks('content');

        return $this->prepareBlocksByLayout($result);
    }

    protected function getSidebarBlocks(): array
    {
        $result = $this->helperLayout->getBlocks('sidebar');

        return $this->prepareBlocksByLayout($result);
    }

    private function prepareBlocksByLayout(array $result): array
    {
        foreach ($result as $key => $block) {
            if (isset($block['layout']) && strpos($block['layout'], $this->layout) === false) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    protected function getLayouts(): array
    {
        return $this->layoutOptions->getDesktopOptions();
    }

    /**
     * @param array $blocks
     * @return array
     */
    private function wrapSkinImages(array $blocks)
    {
        $data = [];
        foreach ($blocks as $block) {
            if (isset($block['backend_image'])) {
                $backendImage = $block['backend_image'];
                $backendImage = $this->getViewFileUrl('Amasty_Blog/' . $backendImage);
                $block['backend_image'] = $backendImage;
            }
            $data[] = $block;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getLayoutConfig()
    {
        $contentBlocks = $this->getContentBlocks();
        $sidebarBlocks = $this->getSidebarBlocks();

        return [
            'content' => $this->wrapSkinImages($contentBlocks),
            'sidebar' => $this->wrapSkinImages($sidebarBlocks),
            'layouts' => $this->getLayouts(),
            'delete_message' => __("Are you sure?"),
        ];
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool|string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $result = false;
        $renderer = $this->getLayout()->createBlock(Renderer::class);

        if ($renderer) {
            $result = $renderer->setElementId($element->getHtmlId())
                ->setElementName($element->getName())
                ->setElementValue($element->getValue())
                ->setLayoutConfig($this->getLayoutConfig())
                ->toHtml();
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $id = $element->getHtmlId();
        $checkboxLabel = '';
        $html = '<td colspan="5">';
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $html .= '<div class="label">';
        $html .= $element->getLabel();
        $html .= '</div>';

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = __('Use Website');
        } elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = __('Use Default');
        }

        $inherit = '';
        if ($addInheritCheckbox) {
            $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        $html .= '<div class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html .= '</div>';

        if ($addInheritCheckbox) {
            $defText = $this->escapeHtml($element->getDefaultValue());
            $html .= '<div class="use-default">';
            $html .= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' " /> ';
            $html .= '<label for="' . $id . '_inherit" class="inherit" title="'
                . $defText . '">' . $checkboxLabel . '</label>';
            $html .= '</div>';
        }

        $html .= "<div class=\"fixed\"></div>";

        $html .= "<div class=\"layout-element\">";
        $html .= $this->_getElementHtml($element);
        $html .= "</div>";
        $html .= "</td>";

        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }
}
