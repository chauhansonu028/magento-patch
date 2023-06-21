<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Adminhtml\System\Config\Field\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class
 */
class Renderer extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    private $elementName;

    /**
     * @var int
     */
    private $elementId;

    /**
     * @var string
     */
    private $elementValue;

    /**
     * @var array
     */
    private $layoutConfig = [];

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("Amasty_Blog::system/config/field/element.phtml");
    }

    /**
     * @return string
     */
    public function getElementValue()
    {
        return $this->elementValue;
    }

    /**
     * @param $elementValue
     *
     * @return $this
     */
    public function setElementValue($elementValue)
    {
        $this->elementValue = $elementValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getElementName()
    {
        return $this->elementName;
    }

    /**
     * @param $elementName
     *
     * @return $this
     */
    public function setElementName($elementName)
    {
        $this->elementName = $elementName;

        return $this;
    }

    /**
     * @return int
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param $elementId
     *
     * @return $this
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setLayoutConfig($config)
    {
        $this->layoutConfig = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getLayoutConfig()
    {
        return $this->layoutConfig;
    }

    /**
     * @return string
     */
    public function getLayoutConfigJson()
    {
        //use object manager to avoid loading dependencies of parent class
        $objectManager = ObjectManager::getInstance();
        $serializer = $objectManager->create(Json::class);

        return $serializer->serialize($this->getLayoutConfig());
    }
}
