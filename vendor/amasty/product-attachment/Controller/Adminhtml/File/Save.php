<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\File;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\FileRepositoryInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\File;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends File
{
    /**
     * @var FileRepositoryInterface
     */
    private $repository;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        FileRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\ProductAttachment\Model\File\File $model */
                $model = $this->fileFactory->create();
                $data = $this->getRequest()->getPostValue();

                if ($fileId = (int)$this->getRequest()->getParam(RegistryConstants::FORM_FILE_ID)) {
                    $model = $this->repository->getById($fileId);
                    if ($fileId != $model->getFileId()) {
                        throw new LocalizedException(__('The wrong item is specified.'));
                    }
                }

                $this->filterData($data);
                $dataToModel['extension_attributes'] = $data['extension_attributes'] ?? [];
                unset($data['extension_attributes']);
                $this->dataObjectHelper->populateWithArray(
                    $model,
                    $dataToModel,
                    FileInterface::class
                );
                $model->addData($data);
                $this->repository->saveAll($model);

                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/edit',
                        [RegistryConstants::FORM_FILE_ID => $model->getId(), '_current' => true]
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set(RegistryConstants::FILE_DATA, $data);

                $resultRedirect = $this->resultRedirectFactory->create();
                if ($fileId = (int)$this->getRequest()->getParam(RegistryConstants::FORM_FILE_ID)) {
                    $resultRedirect->setPath('*/*/edit', [RegistryConstants::FORM_FILE_ID => $fileId]);
                } else {
                    $resultRedirect->setPath('*/*/create');
                }

                return $resultRedirect;
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

    /**
     * @param array $data
     */
    private function filterData(&$data)
    {
        if (!empty($data['fileproducts']['products'])) {
            $productIds = [];
            foreach ($data['fileproducts']['products'] as $product) {
                $productIds[] = (int)$product['entity_id'];
            }
            $data[FileInterface::PRODUCTS] = array_unique($productIds);
        } else {
            $data[FileInterface::PRODUCTS] = [];
        }
    }
}
