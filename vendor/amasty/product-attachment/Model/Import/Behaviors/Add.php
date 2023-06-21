<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Behaviors;

use Amasty\Base\Model\Serializer;
use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\Data\FileInterfaceFactory;
use Amasty\ProductAttachment\Api\FileRepositoryInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\Import\Behaviors\FileType\TypeProcessorProvider;
use Amasty\ProductAttachment\Model\Import\Import as ImportModel;
use Amasty\ProductAttachment\Model\Import\Relation\FileType\ImportFile;
use Amasty\ProductAttachment\Model\Import\Repository as ImportRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Registry;

class Add implements \Amasty\Base\Model\Import\Behavior\BehaviorInterface
{
    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var File
     */
    private $file;

    /**
     * @var ImportRepository
     */
    private $importRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    public function __construct(
        FileInterfaceFactory $fileFactory,
        FileRepositoryInterface $fileRepository,
        ProductRepositoryInterface $productRepository,
        ?File $file, // @deprecated
        Registry $registry,
        ImportRepository $importRepository,
        Serializer $serializer,
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->fileFactory = $fileFactory;
        $this->fileRepository = $fileRepository;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->file = $file;
        $this->importRepository = $importRepository;
        $this->serializer = $serializer;
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    /**
     * @var array
     */
    private $mappingIds = [];

    /**
     * @var array
     */
    private $importFileIdsPath = [];

    /**
     * @param array $importData
     *
     * @return void
     */
    public function execute(array $importData)
    {
        if (empty($importData[0])) {
            return;
        }

        if (empty($this->importFileIdsPath)) {
            $importFiles = $this->importRepository
                ->getImportFilesByImportId((int)$importData[0][ImportModel::IMPORT_ID]);
            $unserializedImportFiles = $this->serializer->unserialize($importFiles);
            foreach ($unserializedImportFiles as $row) {
                $processor = $this->typeProcessorProvider->getProcessorByType($row[FileInterface::ATTACHMENT_TYPE]);
                $this->importFileIdsPath[$row[ImportFile::FILE_ID]] = $processor->getFilePath($row);
            }
            if (!$this->registry->registry('amfile_import_id')) {
                $this->registry->register('amfile_import_id', (int)$importData[0][ImportModel::IMPORT_ID]);
            }
        }

        foreach ($importData as $row) {
            $file = $this->fileFactory->create();

            if (isset($this->mappingIds[$row[ImportModel::IMPORT_FILE_ID]])) {
                $file->setFileId($this->mappingIds[$row[ImportModel::IMPORT_FILE_ID]]);
            } else {
                if (!isset($this->importFileIdsPath[$row[ImportModel::IMPORT_FILE_ID]])) {
                    return;
                }

                $processor = $this->typeProcessorProvider->getProcessorByType($row[FileInterface::ATTACHMENT_TYPE]);
                $processor->setFilePath($file, $this->importFileIdsPath[$row[ImportModel::IMPORT_FILE_ID]]);
            }
            $file->setAttachmentType($row[FileInterface::ATTACHMENT_TYPE]);

            if (!empty($row[FileInterface::LABEL])) {
                $file->setLabel($row[FileInterface::LABEL]);
            } else {
                $file->setLabel(null);
            }

            if (!empty($row[FileInterface::FILENAME])) {
                $file->setFileName($row[FileInterface::FILENAME]);
            } else {
                $file->setFileName(null);
            }

            if ($row[FileInterface::IS_VISIBLE] !== '' && $row[FileInterface::IS_VISIBLE] !== null) {
                $file->setIsVisible(!empty($row[FileInterface::IS_VISIBLE]));
            } else {
                $file->setData(FileInterface::IS_VISIBLE, null);
            }

            if ($row[FileInterface::INCLUDE_IN_ORDER] !== '' && $row[FileInterface::INCLUDE_IN_ORDER] !== null) {
                $file->setIsIncludeInOrder(!empty($row[FileInterface::INCLUDE_IN_ORDER]));
            } else {
                $file->setData(FileInterface::INCLUDE_IN_ORDER, null);
            }

            if ($row[FileInterface::CUSTOMER_GROUPS] !== '' && $row[FileInterface::CUSTOMER_GROUPS] !== null) {
                $customerGroups = explode(',', $row[FileInterface::CUSTOMER_GROUPS]);
                foreach ($customerGroups as &$customerGroup) {
                    $customerGroup = (int)$customerGroup;
                }
                $file->setData(FileInterface::CUSTOMER_GROUPS . '_output', implode(',', $customerGroups));
            } else {
                $file->setData(FileInterface::CUSTOMER_GROUPS . '_output', null);
            }

            if ($row[FileInterface::PRODUCTS] !== '' && $row[FileInterface::PRODUCTS] !== null) {
                $products = explode(',', $row[FileInterface::PRODUCTS]);
                foreach ($products as &$product) {
                    $product = (int)$product;
                }
                $file->setData(FileInterface::PRODUCTS, array_unique($products));
            } else {
                $file->setData(FileInterface::PRODUCTS, null);
                $file->setData('use_default_products', true);
            }

            if (isset($row['product_skus']) && $row['product_skus'] !== '' && $row['product_skus'] !== null) {
                $productSkus = explode(',', $row['product_skus']);
                $products = [];

                foreach ($productSkus as $sku) {
                    try {
                        $products[] = (int)$this->productRepository->get(trim($sku))->getId();
                    } catch (LocalizedException $e) {
                        null;
                    }
                }

                if (!empty($products)) {
                    $this->setFileProducts($file, $products);
                }
            }

            if ($row[FileInterface::CATEGORIES] !== '' && $row[FileInterface::CATEGORIES] !== null) {
                $categories = explode(',', $row[FileInterface::CATEGORIES]);
                foreach ($categories as &$category) {
                    $category = (int)$category;
                }
                $file->setData(FileInterface::CATEGORIES, array_unique($categories));
            } else {
                $file->setData(FileInterface::CATEGORIES, null);
                $file->setData('use_default_categories', true);
            }

            $file = $this->fileRepository->saveAll(
                $file,
                [RegistryConstants::STORE => (int)$row['store_id']]
            );
            if (!isset($this->mappingIds[$row[ImportModel::IMPORT_FILE_ID]])) {
                $this->mappingIds[$row[ImportModel::IMPORT_FILE_ID]] = $file->getFileId();
            }
        }
    }

    public function setFileProducts($file, $products)
    {
        if (is_array($file->getData(FileInterface::PRODUCTS))) {
            $products = array_merge($file->getData(FileInterface::PRODUCTS), $products);
        }

        $file->setData(FileInterface::PRODUCTS, $products);
        $file->setData('use_default_products', false);
    }
}
