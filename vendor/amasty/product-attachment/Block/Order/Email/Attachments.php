<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Block\Order\Email;

class Attachments extends \Amasty\ProductAttachment\Block\Order\AbstractAttachments
{
    public function toHtml()
    {
        if (!$this->configProvider->isShowInOrderEmail()) {
            return '';
        }

        return parent::toHtml();
    }

    public function getBlockTitle()
    {
        return $this->configProvider->getLabelInOrderEmail();
    }

    /**
     * @inheritdoc
     */
    public function getAttachmentsFilter()
    {
        return $this->configProvider->getEmailAttachmentsFilter();
    }

    /**
     * @inheritdoc
     */
    public function getOrderStatuses()
    {
        return $this->configProvider->getEmailOrderStatuses();
    }
}
