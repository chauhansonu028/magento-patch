<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\DataProviders\Frontend;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\File\FileScope\DataProviders\FileScopeDataInterface;
use Amasty\ProductAttachment\Model\File\FileScope\ResourceModel\FileStore;
use Amasty\ProductAttachment\Model\File\FileType\TypeProcessorProvider;
use Amasty\ProductAttachment\Model\Icon\GetIconForFile;
use Amasty\ProductAttachment\Model\SourceOptions\OrderFilterType;
use Magento\Customer\Model\Session;

class File implements FileScopeDataInterface
{
    /**
     * @var FileStore
     */
    private $fileStore;

    /**
     * @var GetIconForFile
     */
    private $getIconForFile;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    public function __construct(
        FileStore $fileStore,
        GetIconForFile $getIconForFile,
        Session $customerSession,
        ConfigProvider $configProvider,
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->fileStore = $fileStore;
        $this->getIconForFile = $getIconForFile;
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    /**
     * @inheritdoc
     */
    public function execute($params)
    {
        /** @var FileInterface $file */
        $file = $params[RegistryConstants::FILE];
        $store = $params[RegistryConstants::STORE];
        $fileStoreData = [];
        if ($store) {
            $fileStoreData = $this->fileStore->getByStoreId($file->getFileId(), $store);
            foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
                $file->setData(
                    RegistryConstants::USE_DEFAULT_PREFIX . $field,
                    (!isset($fileStoreData[$field]) || $fileStoreData[$field] === null)
                );
            }
        }

        $defaultFileStoreData = $this->fileStore->getByStoreId($file->getFileId(), 0);
        foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
            if (isset($fileStoreData[$field])) {
                if ($fileStoreData[$field] === null) {
                    $file->setData($field, $defaultFileStoreData[$field]);
                } else {
                    $file->setData($field, $fileStoreData[$field]);
                }
            } else {
                $file->setData($field, $defaultFileStoreData[$field]);
            }
        }

        return $this->processFileParams($file, $params);
    }

    /**
     * @param FileInterface $file
     * @param $params
     *
     * @return bool|FileInterface
     */
    public function processFileParams(FileInterface $file, $params)
    {
        if (!$file->isVisible()) {
            return false;
        }

        if (isset($params[RegistryConstants::INCLUDE_FILTER])) {
            switch ($params[RegistryConstants::INCLUDE_FILTER]) {
                case OrderFilterType::INCLUDE_IN_ORDER_ONLY:
                    if (!$file->isIncludeInOrder()) {
                        return false;
                    }
                    break;
            }
        } else {
            if ($this->configProvider->excludeIncludeInOrderFiles() && $file->isIncludeInOrder()) {
                return false;
            }
        }
        if ($customerGroups = $file->getCustomerGroups()) {
            if (isset($params[RegistryConstants::CUSTOMER_GROUP])) {
                /** through api */
                if (!in_array($params[RegistryConstants::CUSTOMER_GROUP], $customerGroups)) {
                    return false;
                }
            } else {
                if (!in_array($this->customerSession->getCustomerGroupId(), $customerGroups)) {
                    return false;
                }
            }
        }

        $file->setIconUrl($this->getIconForFile->byFileExtension(
            $file->getFileExtension(),
            $file->getAttachmentType()
        ) ?: false);

        $typeProcessor = $this->typeProcessorProvider->getProcessorByType($file->getAttachmentType());
        $typeProcessor->addFrontendUrl($file, $params);

        return $file;
    }
}
