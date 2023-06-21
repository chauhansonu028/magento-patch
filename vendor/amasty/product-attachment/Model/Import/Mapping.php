<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import;

use Amasty\Base\Model\Import\Mapping\Mapping as MappingBase;
use Amasty\ProductAttachment\Api\Data\FileInterface;

class Mapping extends MappingBase implements \Amasty\Base\Model\Import\Mapping\MappingInterface
{
    /**
     * @var array
     */
    protected $mappings = [
        Import::IMPORT_FILE_ID,
        FileInterface::ATTACHMENT_TYPE,
        Import::IMPORT_ID,
        'store_id',
        FileInterface::FILENAME,
        FileInterface::LABEL,
        FileInterface::CUSTOMER_GROUPS,
        FileInterface::IS_VISIBLE,
        FileInterface::INCLUDE_IN_ORDER,
        FileInterface::PRODUCTS,
        FileInterface::CATEGORIES,
        'product_skus'
    ];

    /**
     * @var string
     */
    protected $masterAttributeCode = Import::IMPORT_FILE_ID;
}
