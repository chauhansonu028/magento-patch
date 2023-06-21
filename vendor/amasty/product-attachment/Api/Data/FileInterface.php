<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @method mixed getData($key = '', $index = null)
 * @method $this setData($key = '', $value = null)
 */
interface FileInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const FILE_ID = 'file_id';
    public const ATTACHMENT_TYPE = 'attachment_type';
    public const FILE_PATH = 'filepath';
    public const LINK = 'link';
    public const EXTENSION = 'extension';
    public const SIZE = 'size';
    public const MIME_TYPE = 'mime_type';
    public const FILENAME = 'filename';
    public const LABEL = 'label';
    public const IS_VISIBLE = 'is_visible';
    public const INCLUDE_IN_ORDER = 'include_in_order';
    public const CUSTOMER_GROUPS = 'customer_groups';
    public const CATEGORIES = 'category_ids';
    public const PRODUCTS = 'product_ids';
    public const ICON_URL = 'icon_url';
    public const FRONTEND_URL = 'frontend_url';
    public const URL_HASH = 'url_hash';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    /**#@-*/

    /**
     * @return int
     */
    public function getFileId();

    /**
     * @param int $fileId
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setFileId($fileId);

    /**
     * @return int
     */
    public function getAttachmentType();

    /**
     * @param int $attachmentType
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setAttachmentType($attachmentType);

    /**
     * @return string
     */
    public function getFilePath();

    /**
     * @param string $filePath
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setFilePath($filePath);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string link
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setLink($link);

    /**
     * @return string
     */
    public function getFileExtension();

    /**
     * @param string $extension
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setFileExtension($extension);

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @param string $mimeType
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setMimeType($mimeType);

    /**
     * @return int
     */
    public function getFileSize();

    /**
     * @param int $fileSize
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setFileSize($fileSize);

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @param string $fileName
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setFileName($fileName);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setLabel($label);

    /**
     * @return string[]
     */
    public function getCustomerGroups();

    /**
     * @param string[] $customerGroups
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setCustomerGroups($customerGroups);

    /**
     * @return bool
     */
    public function isVisible();

    /**
     * @param bool $isVisible
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setIsVisible($isVisible);

    /**
     * @return bool
     */
    public function isIncludeInOrder();

    /**
     * @param bool $isIncludeInOrder
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setIsIncludeInOrder($isIncludeInOrder);

    /**
     * @return string
     */
    public function getIconUrl();

    /**
     * @param string $iconUrl
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setIconUrl($iconUrl);

    /**
     * @return string
     */
    public function getFrontendUrl();

    /**
     * @param string $frontendUrl
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setFrontendUrl($frontendUrl);

    /**
     * @return string
     */
    public function getUrlHash();

    /**
     * @param string $urlHash
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileInterface
     */
    public function setUrlHash($urlHash);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\ProductAttachment\Api\Data\FileExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\ProductAttachment\Api\Data\FileExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(FileExtensionInterface $extensionAttributes);
}
