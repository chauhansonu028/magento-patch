<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Blog\Config;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param mixed $source
     *
     * @return array
     */
    public function convert($source)
    {
        $config = [];

        $this->addNodes($source, $config, 'sidebar');
        $this->addNodes($source, $config, 'content');

        $sidebarNodes = $source->getElementsByTagName('color_schemes');
        /** @var $sidebarNodes \DOMElement */
        foreach ($sidebarNodes as $sidebarNode) {
            $sidebar = [];
            /** @var $sidebarNode \DOMElement */
            foreach ($sidebarNode->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement && $childNode->nodeType == XML_ELEMENT_NODE) {
                    $sidebar[$childNode->tagName] = $this->parseNode(['label'], $childNode);

                    foreach ($childNode->getElementsByTagName('data') as $item) {
                        $elements = ['textcolor', 'textcolor2', 'hicolor'];
                        $sidebar[$childNode->tagName]['data'] = $this->parseNode($elements, $childNode);
                    }
                }
            }
            $config['color_schemes'] = $sidebar;
        }

        return $config;
    }

    /**
     * @param $source
     * @param $config
     * @param $nodeType
     */
    private function addNodes($source, &$config, $nodeType)
    {
        $nodes = $source->getElementsByTagName($nodeType);
        foreach ($nodes as $sidebarNode) {
            $sidebar = [];
            /** @var $sidebarNode \DOMElement */
            foreach ($sidebarNode->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement && $childNode->nodeType == XML_ELEMENT_NODE) {
                    $elements = ['label', 'frontend_block', 'backend_image', 'sort_order', 'layout'];
                    $sidebar[$childNode->tagName] = $this->parseNode($elements, $childNode);
                }
            }
            $config[$nodeType] = $sidebar;
        }
    }

    /**
     * @param $elements
     * @param $childNode
     * @return array
     */
    private function parseNode($elements, $childNode)
    {
        $result = [];
        /** @var $childNode \DOMElement */
        foreach ($elements as $element) {
            $item = $childNode->getElementsByTagName($element)->item(0);
            if ($item) {
                $result[$element] = $item->nodeValue;
            }
        }

        return $result;
    }
}
