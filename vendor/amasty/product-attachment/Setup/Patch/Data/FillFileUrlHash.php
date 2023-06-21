<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Setup\Patch\Data;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FillFileUrlHash implements DataPatchInterface
{
    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var File
     */
    private $fileResource;

    public function __construct(
        ResourceInterface $moduleResource,
        File $fileResource
    ) {
        $this->moduleResource = $moduleResource;
        $this->fileResource = $fileResource;
    }

    public function apply()
    {
        $setupDataVersion = $this->moduleResource->getDataVersion('Amasty_ProductAttachment');

        // Check if module was already installed or not.
        // If setup_version present in DB then we don't need to install fixtures, because setup_version is a marker.
        if ($setupDataVersion && version_compare($setupDataVersion, '2.3.0', '<')) {
            $this->fileResource->getConnection()->update(
                $this->fileResource->getTable(File::TABLE_NAME),
                [FileInterface::URL_HASH => new \Zend_Db_Expr('md5(uuid())')]
            );
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
