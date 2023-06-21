<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'amfile/';

    public const XPATH_ENABLED = 'general/enabled';
    public const ADD_CATEGORIES_FILES_TO_PRODUCTS = 'general/add_categories_files';
    public const EXCLUDE_INCLUDE_IN_ORDER_FILES = 'general/exclude_include_in_order_files';
    public const URL_TYPE = 'general/url_type';

    public const HEALTHCHECK_STATUS = 'healthcheck/enable_healthcheck';
    public const INVALID_ATTACHMENTS_NOTIFICATION_RECIPIENTS = 'healthcheck/notofications_recipients';

    public const DETECT_MIME_TYPE = 'additional/detect_mime';

    public const BLOCK_TITLE = 'product_tab/block_label';
    public const BLOCK_ENABLED = 'product_tab/block_enabled';
    public const BLOCK_SORT_ORDER = 'product_tab/block_sort_order';
    public const BLOCK_CUSTOMER_GROUPS = 'product_tab/customer_group';
    public const SHOW_ICON = 'product_tab/block_fileicon';
    public const SHOW_FILESIZE = 'product_tab/block_filesize';

    public const SHOW_IN_ORDER_VIEW = 'order_view/show_attachments';
    public const ORDER_VIEW_LABEL = 'order_view/label';
    public const ORDER_VIEW_ORDER_STATUS = 'order_view/order_status';
    public const ORDER_VIEW_SHOW_FILESIZE = 'order_view/filesize';
    public const ORDER_VIEW_SHOW_ICON = 'order_view/fileicon';
    public const ORDER_VIEW_ATTACHMENTS_FILTER = 'order_view/include_attachments_filter';

    public const SHOW_IN_ORDER_EMAIL = 'order_email/show_attachments';
    public const ORDER_EMAIL_LABEL = 'order_email/label';
    public const ORDER_EMAIL_ATTACHMENTS_FILTER = 'order_email/include_attachments_filter';
    public const ORDER_EMAIL_ORDER_STATUS = 'order_email/order_status';

    public const BLOCK_LOCATION = 'block/block_location';

    public function isEnabled(): bool
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }

    public function getBlockTitle(): string
    {
        return (string)$this->getValue(self::BLOCK_TITLE);
    }

    public function getUrlType(): int
    {
        return (int)$this->getValue(self::URL_TYPE);
    }

    public function isHealthcheckEnabled(int $websiteId): bool
    {
        return $this->isSetFlag(self::HEALTHCHECK_STATUS, $websiteId, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getInvalidAttachmentsNotificationRecipients(int $scopeId): array
    {
        $value = $this->getValue(
            self::INVALID_ATTACHMENTS_NOTIFICATION_RECIPIENTS,
            $scopeId,
            ScopeInterface::SCOPE_WEBSITE
        );

        return ($value) ? explode(',', $value) : [];
    }

    public function getBlockCustomerGroups(): string
    {
        return (string)$this->getValue(self::BLOCK_CUSTOMER_GROUPS);
    }

    public function detectMimeType(): bool
    {
        return $this->isSetFlag(self::DETECT_MIME_TYPE);
    }

    public function getMimeTypeMapForAutodetect(): array
    {
        return ['text/csv' => 'text/plain'];
    }

    public function addCategoriesFilesToProducts(): bool
    {
        return $this->isSetFlag(self::ADD_CATEGORIES_FILES_TO_PRODUCTS);
    }

    public function isBlockEnabled(): bool
    {
        return $this->isSetFlag(self::BLOCK_ENABLED);
    }

    public function getBlockSortOrder(): int
    {
        return (int)$this->getValue(self::BLOCK_SORT_ORDER);
    }

    public function isShowIcon(): bool
    {
        return $this->isSetFlag(self::SHOW_ICON);
    }

    public function isShowFilesize(): bool
    {
        return $this->isSetFlag(self::SHOW_FILESIZE);
    }

    public function isShowInOrderView(): bool
    {
        return $this->isSetFlag(self::SHOW_IN_ORDER_VIEW);
    }

    public function getLabelInOrderView(): string
    {
        return (string)$this->getValue(self::ORDER_VIEW_LABEL);
    }

    public function getViewOrderStatuses(): array
    {
        $orderStatuses = $this->getValue(self::ORDER_VIEW_ORDER_STATUS);

        return empty($orderStatuses) ? [] : array_map('trim', explode(',', $orderStatuses));
    }

    public function isShowIconInOrderView(): bool
    {
        return $this->isSetFlag(self::ORDER_VIEW_SHOW_ICON);
    }

    public function isShowFilesizeInOrderView(): bool
    {
        return $this->isSetFlag(self::ORDER_VIEW_SHOW_FILESIZE);
    }

    public function getViewAttachmentsFilter(): int
    {
        return (int)$this->getValue(self::ORDER_VIEW_ATTACHMENTS_FILTER);
    }

    public function isShowInOrderEmail(): bool
    {
        return $this->isSetFlag(self::SHOW_IN_ORDER_EMAIL);
    }

    public function getLabelInOrderEmail(): string
    {
        return (string)$this->getValue(self::ORDER_EMAIL_LABEL);
    }

    public function getEmailAttachmentsFilter(): int
    {
        return (int)$this->getValue(self::ORDER_EMAIL_ATTACHMENTS_FILTER);
    }

    public function getEmailOrderStatuses(): array
    {
        $orderStatuses = $this->getValue(self::ORDER_EMAIL_ORDER_STATUS);

        return empty($orderStatuses) ? [] : array_map('trim', explode(',', $orderStatuses));
    }

    public function getBlockLocation(): string
    {
        return $this->getValue(self::BLOCK_LOCATION);
    }

    public function excludeIncludeInOrderFiles(): bool
    {
        return !$this->isSetFlag(self::EXCLUDE_INCLUDE_IN_ORDER_FILES);
    }
}
