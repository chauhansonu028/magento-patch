<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Api\Data;

interface IconInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ICON_ID = 'icon_id';

    public const FILE_TYPE = 'filetype';

    public const IMAGE = 'image';

    public const IS_ACTIVE = 'is_active';

    public const EXTENSION = 'extension';

    public const ATTACHMENT_TYPE = 'attachment_type';
    /**#@-*/

    /**
     * @return int
     */
    public function getIconId();

    /**
     * @param int $iconId
     *
     * @return \Amasty\ProductAttachment\Api\Data\IconInterface
     */
    public function setIconId($iconId);

    /**
     * @return string
     */
    public function getFileType();

    /**
     * @param string $fileType
     *
     * @return \Amasty\ProductAttachment\Api\Data\IconInterface
     */
    public function setFileType($fileType);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string $image
     *
     * @return \Amasty\ProductAttachment\Api\Data\IconInterface
     */
    public function setImage($image);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $isActive
     *
     * @return \Amasty\ProductAttachment\Api\Data\IconInterface
     */
    public function setIsActive($isActive);

    /**
     * @return array
     */
    public function getExtension();

    /**
     * @param array $extensions
     *
     * @return \Amasty\ProductAttachment\Api\Data\IconInterface
     */
    public function setExtension($extensions);

    /**
     * @return int
     */
    public function getAttachmentType(): int;

    /**
     * @param int $type
     *
     * @return \Amasty\ProductAttachment\Api\Data\IconInterface
     */
    public function setAttachmentType(int $type): IconInterface;
}
