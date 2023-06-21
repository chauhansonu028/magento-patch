<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

use Amasty\ProductAttachment\Api\Data\IconInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\Icon;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\Filesystem\File;
use Magento\Backend\App\Action\Context;
use Amasty\ProductAttachment\Model\Icon\IconFactory as IconFactory;
use Amasty\ProductAttachment\Api\IconRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Icon
{
    /**
     * @var IconRepositoryInterface
     */
    private $repository;

    /**
     * @var IconFactory
     */
    private $iconFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        Context $context,
        IconFactory $iconFactory,
        IconRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        File $file
    ) {
        parent::__construct($context);
        $this->iconFactory = $iconFactory;
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->file = $file;
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\ProductAttachment\Model\Icon\Icon $model */
                $model = $this->iconFactory->create();
                $data = $this->getRequest()->getPostValue();

                if ($iconId = (int)$this->getRequest()->getParam(RegistryConstants::FORM_ICON_ID)) {
                    $model = $this->repository->getById($iconId);
                    if ($iconId != $model->getIconId()) {
                        throw new LocalizedException(__('The wrong item is specified.'));
                    }
                }

                $this->filterData($data);
                $model->addData($data);
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/edit',
                        [RegistryConstants::FORM_ICON_ID => $model->getId()]
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set(RegistryConstants::ICON_DATA, $data);

                $resultRedirect = $this->resultRedirectFactory->create();
                if ($iconId = (int)$this->getRequest()->getParam(RegistryConstants::FORM_ICON_ID)) {
                    $resultRedirect->setPath('*/*/edit', [RegistryConstants::FORM_ICON_ID => $iconId]);
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
        if (isset($data[RegistryConstants::ICON_FILE_KEY]) && is_array($data[RegistryConstants::ICON_FILE_KEY])) {
            if (isset($data[RegistryConstants::ICON_FILE_KEY][0]['name'])
                && isset($data[RegistryConstants::ICON_FILE_KEY][0]['tmp_name'])
            ) {
                $uploadFileData = $this->file->getUploadFileData();
                $uploadFileData->setTmpFileName($data[RegistryConstants::ICON_FILE_KEY][0]['file']);
                if ($this->file->save($uploadFileData, \Amasty\ProductAttachment\Model\Filesystem\Directory::ICON)) {
                    $data[IconInterface::IMAGE] = $uploadFileData->getFileName()
                        . '.' . $uploadFileData->getExtension();
                } else {
                    $data[IconInterface::IMAGE] = '';
                }
            }
        } else {
            $data[IconInterface::IMAGE] = '';
        }

        if (!empty($data['extensions'])) {
            $result = [];
            foreach ($data['extensions'] as $extension) {
                if (!empty($extension[IconInterface::EXTENSION])) {
                    $result[] = $extension[IconInterface::EXTENSION];
                }
            }
            $data[IconInterface::EXTENSION] = $result;
        }
    }
}
