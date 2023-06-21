<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\DataProvider;

use Amasty\Base\Model\Serializer;
use Amasty\ProductAttachment\Model\Filesystem\ImportFilesScanner;
use Amasty\ProductAttachment\Model\Icon\GetIconForFile;
use Amasty\ProductAttachment\Model\Icon\ResourceModel\Icon;
use Amasty\ProductAttachment\Model\Import\Import;
use Amasty\ProductAttachment\Model\Import\Repository;
use Amasty\ProductAttachment\Model\Import\ResourceModel\ImportCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Io\File;

class Files extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ImportFilesScanner
     */
    private $importFilesScanner;

    /**
     * @var Icon
     */
    private $iconResource;

    /**
     * @var GetIconForFile
     */
    private $iconForFile;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        File $file,
        ImportCollectionFactory $importCollectionFactory,
        Repository $repository,
        ImportFilesScanner $importFilesScanner,
        GetIconForFile $iconForFile,
        Icon $iconResource,
        $name,
        $primaryFieldName,
        $requestFieldName,
        Serializer $serializer,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $importCollectionFactory->create();
        $this->importFilesScanner = $importFilesScanner;
        $this->iconResource = $iconResource;
        $this->iconForFile = $iconForFile;
        $this->repository = $repository;
        $this->file = $file;
        $this->serializer = $serializer;
    }

    public function getData()
    {
        $data = parent::getData();
        if (empty($data['items'])) {
            $data = [];
            $key = null;
            $data[$key] = [Import::IMPORT_ID => $key];
        } else {
            $key = $data['items'][0][Import::IMPORT_ID];
            $data[$key] = $data['items'][0];
        }

        if ($uploadedFiles = $this->importFilesScanner->execute()) {
            $allowedExtensions = $this->iconResource->getAllowedExtensions();
            $fileId = 100000;
            foreach ($uploadedFiles as $file) {
                list($fileName, $baseName, $extension) = $this->extractPathInfo($file);
                if (in_array($extension, $allowedExtensions)) {
                    $data[$key]['attachments']['files'][] = [
                        'show_file_id' => 'New File',
                        'file_id' => $fileId++,
                        'icon' => $this->iconForFile->byFileExtension($extension) ?: false,
                        'extension' => $extension,
                        'label' => $fileName,
                        'filename' => $fileName,
                        'include_in_order' => '0',
                        'is_visible' => '1',
                        'customer_groups' => '',
                        'filepath' => $baseName
                    ];
                }
            }
        }

        if ($key) {
            $importFiles = $this->repository->getImportFilesByImportId((int)$key);
            $unserializedImportFiles = $this->serializer->unserialize($importFiles) ?: [];
            foreach ($unserializedImportFiles as $id => $importFile) {
                $data[$key]['attachments']['files'][] = [
                    'show_file_id' => $importFile['show_file_id'],
                    'file_id' => $id,
                    'icon' => $this->iconForFile->byFileExtension(
                        $importFile['extension'],
                        $importFile['attachment_type']
                    ) ?: false,
                    'extension' => $importFile['extension'],
                    'label' => $importFile['label'],
                    'filename' => $importFile['filename'],
                    'include_in_order' => $importFile['include_in_order'] ? '1' : '0',
                    'is_visible' => $importFile['is_visible'] ? '1' : '0',
                    'customer_groups' => $importFile['customer_groups']
                ];
            }
        }

        return $data;
    }

    private function extractPathInfo($filePath)
    {
        $pathInfo = $this->file->getPathInfo($filePath);

        return [
            !empty($pathInfo['filename']) ? $pathInfo['filename'] : '',
            !empty($pathInfo['basename']) ? $pathInfo['basename'] : '',
            !empty($pathInfo['extension']) ? $pathInfo['extension'] : '',
        ];
    }
}
