<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\Base\Model\Serializer;
use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\Import;
use Amasty\ProductAttachment\Model\Import\Import as ImportModel;
use Amasty\ProductAttachment\Model\Import\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Driver\File as CsvFile;

class Generate extends Import
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CsvFile
     */
    private $file;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Repository $repository,
        CsvFile $file,
        FileFactory $fileFactory,
        Action\Context $context,
        Serializer $serializer
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->file = $file;
        $this->fileFactory = $fileFactory;
        $this->serializer = $serializer;
    }

    public function execute()
    {
        if ($importId = $this->getRequest()->getParam('import_id')) {
            if ($import = $this->repository->getById((int)$importId)) {
                $storeIds = [];
                if (empty($import->getStoreIds())) {
                    $storeIds[] = 0;
                } else {
                    $storeIds = $import->getStoreIds();
                }

                if (!in_array(0, $storeIds)) {
                    $storeIds = array_merge([0], $storeIds);
                }
                $result = [
                    [
                        ImportModel::IMPORT_FILE_ID,
                        FileInterface::ATTACHMENT_TYPE,
                        ImportModel::IMPORT_ID,
                        'store_id',
                        FileInterface::FILENAME,
                        FileInterface::LABEL,
                        FileInterface::CUSTOMER_GROUPS,
                        FileInterface::IS_VISIBLE,
                        FileInterface::INCLUDE_IN_ORDER,
                        FileInterface::PRODUCTS,
                        FileInterface::CATEGORIES,
                        'product_skus'
                    ]
                ];
                $importFiles = $this->repository->getImportFilesByImportId($import->getImportId());
                $unserializedImportFiles = $this->serializer->unserialize($importFiles);
                foreach ($unserializedImportFiles as $importFile) {
                    foreach ($storeIds as $storeId) {
                        if ($storeId == 0) {
                            $result[] = [
                                ImportModel::IMPORT_FILE_ID     => $importFile[FileInterface::FILE_ID],
                                FileInterface::ATTACHMENT_TYPE  => $importFile[FileInterface::ATTACHMENT_TYPE],
                                ImportModel::IMPORT_ID          => $importFile[ImportModel::IMPORT_ID],
                                'store_id'                      => $storeId,
                                FileInterface::FILENAME         => $importFile[FileInterface::FILENAME],
                                FileInterface::LABEL            => $importFile[FileInterface::LABEL],
                                FileInterface::CUSTOMER_GROUPS  => $importFile[FileInterface::CUSTOMER_GROUPS],
                                FileInterface::IS_VISIBLE       => (int)$importFile[FileInterface::IS_VISIBLE],
                                FileInterface::INCLUDE_IN_ORDER => (int)$importFile[FileInterface::INCLUDE_IN_ORDER],
                                FileInterface::PRODUCTS         => '',
                                FileInterface::CATEGORIES       => '',
                                'product_skus' => ''
                            ];
                        } else {
                            $result[] = [
                                ImportModel::IMPORT_FILE_ID     => $importFile[FileInterface::FILE_ID],
                                FileInterface::ATTACHMENT_TYPE  => $importFile[FileInterface::ATTACHMENT_TYPE],
                                ImportModel::IMPORT_ID          => $importFile[ImportModel::IMPORT_ID],
                                'store_id'                      => $storeId,
                                FileInterface::FILENAME         => '',
                                FileInterface::LABEL            => '',
                                FileInterface::CUSTOMER_GROUPS  => '',
                                FileInterface::IS_VISIBLE       => '',
                                FileInterface::INCLUDE_IN_ORDER => '',
                                FileInterface::PRODUCTS         => '',
                                FileInterface::CATEGORIES       => '',
                                'product_skus' => ''
                            ];
                        }
                    }
                }
                $resource = $this->file->fileOpen('php://memory', 'a+');
                foreach ($result as $row) {
                    $this->file->filePutCsv($resource, $row);
                }
                $this->file->fileSeek($resource, 0);
                $csvContent = '';
                while (!$this->file->endOfFile($resource)) {
                    $csvContent .= $this->file->fileRead($resource, 1024);
                }

                $this->fileFactory->create(
                    'amfile_import_' . $importId . '.csv',
                    null,
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'application/octet-stream',
                    strlen($csvContent)
                );
                /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
                $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
                $resultRaw->setContents($csvContent);

                return $resultRaw;
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
