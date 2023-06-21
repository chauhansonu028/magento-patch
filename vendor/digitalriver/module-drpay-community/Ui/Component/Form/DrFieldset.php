<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Ui\Component\Form;

use Digitalriver\DrPay\Helper\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class Fieldset
 * @package Digitalriver\DrPay\Ui\Component\Form
 */
class DrFieldSet extends Fieldset
{
    /**
     * @var \Digitalriver\DrPay\Helper\Config
     */
    protected $drConfig;

    /**
     * RuleInformationFieldset constructor.
     * @param ContextInterface $context
     * @param \Digitalriver\DrPay\Helper\Config $drConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Digitalriver\DrPay\Helper\Config $drConfig,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->drConfig = $drConfig;
    }

    /**
     * hide or show fieldset based on module enabled or disabled.
     */
    public function prepare()
    {
        $visiable = false;
        $config = $this->getData('config');

        if( $this->drConfig->getIsEnabled() ) {
            $visiable = true;
        }

        $config['visible'] = $visiable;
        $this->setData('config', $config);
        
        parent::prepare();
    }
}
