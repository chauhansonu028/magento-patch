<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\Base\Model\Serializer;
use Amasty\ProductAttachment\Controller\Adminhtml\Import;
use Amasty\ProductAttachment\Model\Import\Import as ImportModel;
use Amasty\ProductAttachment\Model\Import\ImportFactory;
use Amasty\ProductAttachment\Model\Import\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Save extends Import
{
    /**
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ImportFactory $importFactory,
        Repository $repository,
        Action\Context $context,
        Serializer $serializer
    ) {
        parent::__construct($context);
        $this->importFactory = $importFactory;
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                if (isset($data['filesData'])) {
                    $data = json_decode($data['filesData'], true);
                }
                if (isset($data['step'])) {
                    switch ($data['step']) {
                        case '1':
                            return $this->processFilesStep($data);
                        case '2':
                            return $this->processStoresStep($data);
                    }
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

    public function processFilesStep($filesData)
    {
        /** @var \Amasty\ProductAttachment\Model\Import\Import $model */
        $model = $this->importFactory->create();
        if ($importId = (int)$this->getRequest()->getParam(ImportModel::IMPORT_ID)) {
            $model = $this->repository->getById($importId);
            if ($importId != $model->getImportId()) {
                throw new LocalizedException(__('The wrong item is specified.'));
            }
        }

        $model->addData($filesData);
        $this->repository->save($model);
        if (!empty($filesData['attachments'])) {
            $files = $this->repository->getImportFilesData(
                $model->getImportId(),
                $filesData['attachments']['files']
            );
            $filesData = [];
            foreach ($files as $id => $file) {
                $filesData[$id] = $file->getData();
            }
            $model->setImportFile($this->serializer->serialize($filesData));
            $this->repository->save($model);
        }

        return $this->resultRedirectFactory->create()->setPath(
            '*/*/store',
            [ImportModel::IMPORT_ID => $model->getId()]
        );
    }

    public function processStoresStep($data)
    {
        if ($importId = (int)$this->getRequest()->getParam(ImportModel::IMPORT_ID)) {
            $model = $this->repository->getById($importId);
            if ($importId != $model->getImportId()) {
                throw new LocalizedException(__('The wrong item is specified.'));
            }
        } else {
            throw new LocalizedException(__('The wrong item is specified.'));
        }

        $model->addData($data);
        $this->repository->save($model);

        return $this->resultRedirectFactory->create()->setPath(
            '*/*/fileimport',
            [ImportModel::IMPORT_ID => $model->getId()]
        );
    }
}
