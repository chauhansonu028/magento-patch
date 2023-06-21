<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Api\Data;

interface FileScopeInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const FILE_STORE_ID = 'file_store_id';
    public const FILE_STORE_CATEGORY_ID = 'file_store_category_id';
    public const FILE_STORE_PRODUCT_ID = 'file_store_product_id';
    public const FILE_STORE_CATEGORY_PRODUCT_ID = 'file_store_category_product_id';
    public const FILE_ID = 'file_id';
    public const STORE_ID = 'store_id';
    public const PRODUCT_ID = 'product_id';
    public const CATEGORY_ID = 'category_id';
    public const FILENAME = 'filename';
    public const LABEL = 'label';
    public const IS_VISIBLE = 'is_visible';
    public const INCLUDE_IN_ORDER = 'include_in_order';
    public const CUSTOMER_GROUPS = 'customer_groups';
    public const POSITION = 'position';
    /**#@-*/
}
