<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Base\Model\Serializer;
use Amasty\ProductAttachment\Model\Import\Import as ImportModel;
use Amasty\ProductAttachment\Model\Import\ResourceModel\Import as ImportResource;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateImportFileData implements DataPatchInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        Serializer $serializer,
        ResourceConnection $resourceConnection,
        File $file
    ) {
        $this->serializer = $serializer;
        $this->resourceConnection = $resourceConnection;
        $this->file = $file;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $importFileTable = $this->resourceConnection->getTableName('amasty_file_import_file');
        $importTable = $this->resourceConnection->getTableName(ImportResource::TABLE_NAME);
        if ($connection->isTableExists($importFileTable)) {
            if (!$connection->tableColumnExists($importTable, ImportModel::IMPORT_FILE)) {
                $connection->addColumn(
                    $importTable,
                    ImportModel::IMPORT_FILE,
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Import File Information'
                    ]
                );
            }
            $select = $connection->select()->from($importFileTable);

            $filesData = [];
            foreach ($connection->fetchAssoc($select) as $id => $file) {
                if (isset($file['import_id'])) {
                    $filesData[$file['import_id']][$id] = [
                        'filepath' => $file['filepath'],
                        'link' => '',
                        'customer_groups' => $file['customer_groups'],
                        'attachment_type' => AttachmentType::FILE,
                        'file_id' => $id,
                        'show_file_id' => $id,
                        'import_id' => $file['import_id'],
                        'extension' => $this->file->getPathInfo($file['filepath'])['extension'],
                        'label' => $file['label'],
                        'filename' => $file['filename'],
                        'include_in_order' => $file['include_in_order'],
                        'is_visible' => $file['is_visible']
                    ];
                }
            }

            foreach ($filesData as $id => $data) {
                $connection->update(
                    $importTable,
                    [ImportModel::IMPORT_FILE => $this->serializer->serialize($data)],
                    [ImportModel::IMPORT_ID . ' = ?' => $id]
                );
            }
        }

        return $this;
    }
}
