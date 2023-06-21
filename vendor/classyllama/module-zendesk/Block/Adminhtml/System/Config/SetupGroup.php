<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Zendesk\Zendesk\Helper\ZendeskApp as ZendeskAppHelper;

class SetupGroup extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var ZendeskAppHelper
     */
    protected $zendeskAppHelper;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param ZendeskAppHelper $zendeskAppHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ZendeskAppHelper $zendeskAppHelper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->zendeskAppHelper = $zendeskAppHelper;
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        if ($this->zendeskAppHelper->isZendeskAppInstalled()) {
            return '';
        }
        return parent::render($element);
    }
}
