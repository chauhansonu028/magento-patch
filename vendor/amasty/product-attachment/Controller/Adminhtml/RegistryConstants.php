<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml;

use Amasty\ProductAttachment\Api\Data\IconInterface;
use Amasty\ProductAttachment\Api\Data\FileInterface;

class RegistryConstants
{
    /**#@+
     * Constants defined for dataPersistor
     */
    public const ICON_DATA = 'iconData';
    public const FILE_DATA = 'fileData';
    /**#@-*/

    /**#@+
     * Constants defined for custom fields
     */
    public const ICON_FILE_KEY = IconInterface::IMAGE . 'file';
    public const FILE_KEY = 'file';
    public const CATEGORY_FILE_KEY = 'category_icon';
    /**#@-*/

    /**#@+
     * Constants defined for form url ids
     */
    public const FORM_ICON_ID = 'icon_id';
    public const FORM_FILE_ID = 'file_id';
    /**#@-*/

    /**#@+
     * Constants defined for FileScopeDataProvider keys
     */
    public const FILE = 'file';
    public const FILES = 'files';
    public const FILE_IDS = 'file_ids';
    public const FILE_IDS_ORDER = 'file_ids_order';
    public const FILES_LIMIT = 'files_limit';
    public const STORE = 'store';
    public const CATEGORY = 'category';
    public const PRODUCT = 'product';
    public const PRODUCT_CATEGORIES = 'product_categories';
    public const EXTRA_URL_PARAMS = 'url_params';
    public const INCLUDE_FILTER = 'include_filter';
    public const CUSTOMER_GROUP = 'customer_group';
    public const TO_DELETE = 'to_delete';
    public const EXCLUDE_FILES = 'exclude_files';
    /**#@-*/

    public const USE_DEFAULT_FIELDS = [
        FileInterface::FILENAME,
        FileInterface::LABEL,
        FileInterface::IS_VISIBLE,
        FileInterface::INCLUDE_IN_ORDER,
        FileInterface::CUSTOMER_GROUPS,
    ];

    public const USE_DEFAULT_PREFIX = 'set_use_default_';

    public const ICON_EXTENSIONS = ['jpg', 'png', 'jpeg', 'bmp'];
}
