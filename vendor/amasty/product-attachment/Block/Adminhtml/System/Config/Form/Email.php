<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Email extends Field
{
    protected function _getElementHtml(AbstractElement $element): string
    {
        $js = '<script type="text/javascript">
            require([
                "Magento_Ui/js/lib/view/utils/async",
                "Amasty_ProductAttachment/js/form/tag-it"
            ], function ($) {
                "use strict";

                var input = "#' . $element->getHtmlId() . '";
                $.async(input, (function() {
                    $(input).tagit();
                }));
            });
            </script>';
        $element->setAfterElementJs($js);
        $element->setData('class', "amfiles-input");

        return parent::_getElementHtml($element);
    }
}
