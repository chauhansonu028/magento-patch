<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\File;

use Amasty\Base\Model\Response\OctetResponseInterface;
use Amasty\Base\Model\Response\OctetResponseInterfaceFactory;
use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\FileRepositoryInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProvider;
use Amasty\ProductAttachment\Model\Filesystem\Directory;
use Amasty\ProductAttachment\Model\Report\ItemFactory;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;
use Amasty\ProductAttachment\Model\SourceOptions\DownloadSource;
use Amasty\ProductAttachment\Model\SourceOptions\OrderFilterType;
use Amasty\ProductAttachment\Model\SourceOptions\UrlType;
use Magento\Customer\Model\Session;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class Download extends Action\Action
{
    /**
     * @var FileScopeDataProvider
     */
    private $fileScopeDataProvider;

    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @var DownloadHelper
     */
    private $downloadHelper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ItemFactory
     */
    private $reportItemFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    private $mediaReader;

    /**
     * @var OctetResponseInterfaceFactory
     */
    private $fileResponseFactory;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileScopeDataProvider $fileScopeDataProvider,
        DownloadHelper $downloadHelper,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        ItemFactory $reportItemFactory,
        Session $customerSession,
        OctetResponseInterfaceFactory $fileResponseFactory,
        Filesystem $filesystem,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->fileScopeDataProvider = $fileScopeDataProvider;
        $this->fileRepository = $fileRepository;
        $this->downloadHelper = $downloadHelper;
        $this->configProvider = $configProvider;
        $this->reportItemFactory = $reportItemFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->mediaReader = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->fileResponseFactory = $fileResponseFactory;
    }

    /**
     * @param FileInterface $file
     */
    public function processFile($file)
    {
        if ($file->getAttachmentType() === AttachmentType::FILE) {
            $filePath = $this->mediaReader->getAbsolutePath(
                Directory::DIRECTORY_CODES[Directory::ATTACHMENT] . DIRECTORY_SEPARATOR . $file->getFilePath()
            );
            $fileType = OctetResponseInterface::FILE;
        } else {
            $filePath = $file->getLink();
            $fileType = OctetResponseInterface::FILE_URL;
        }

        $fileResponse = $this->fileResponseFactory->create($filePath, $fileType);

        if ($this->configProvider->detectMimeType() && !empty($file->getMimeType())) {
            $mimeTypeMapForAutodetect = $this->configProvider->getMimeTypeMapForAutodetect();
            $contentType = isset($mimeTypeMapForAutodetect[$file->getMimeType()])
                ? $mimeTypeMapForAutodetect[$file->getMimeType()]
                : $file->getMimeType();
        } else {
            $contentType = 'application/octet-stream';
        }

        $fileResponse->setContentType($contentType);
        $fileResponse->setFileName($file->getFileName() . '.' . $file->getFileExtension());

        return $fileResponse;
    }

    public function execute()
    {
        $fileId = $this->getRequest()->getParam('file', 0);
        if ($fileId) {
            try {
                if ($this->configProvider->getUrlType() === UrlType::ID) {
                    $file = $this->fileRepository->getById((int)$fileId);
                } else {
                    $file = $this->fileRepository->getByHash($fileId);
                }

                $params = [
                    RegistryConstants::STORE => $this->storeManager->getStore()->getId(),
                    RegistryConstants::FILE => $file,
                    RegistryConstants::INCLUDE_FILTER => OrderFilterType::ALL_ATTACHMENTS
                ];
                if ($categoryId = $this->getRequest()->getParam('category')) {
                    $params[RegistryConstants::CATEGORY] = (int)$categoryId;
                } elseif ($productId = $this->getRequest()->getParam('product')) {
                    $params[RegistryConstants::PRODUCT] = (int)$productId;
                }
                $file = $this->fileScopeDataProvider->execute($params, 'downloadFile');

                if ($file) {
                    $this->saveStat();
                    try {
                        return $this->processFile($file);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        null;
                    }
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                null;
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
    }

    public function saveStat()
    {
        /** @var \Amasty\ProductAttachment\Model\Report\Item $reportItem */
        $reportItem = $this->reportItemFactory->create();
        $reportItem->setFileId($this->getRequest()->getParam('file'))
            ->setStoreId($this->storeManager->getStore()->getId());

        if ($this->getRequest()->getParam('category')) {
            $reportItem->setCategoryId($this->getRequest()->getParam('category'))
                ->setDownloadSource(DownloadSource::CATEGORY);
        } elseif ($this->getRequest()->getParam('product')) {
            $reportItem->setProductId($this->getRequest()->getParam('product'))
                ->setDownloadSource(DownloadSource::PRODUCT);
        } elseif ($this->getRequest()->getParam('order')) {
            $reportItem->setOrderId($this->getRequest()->getParam('order'))
                ->setDownloadSource(DownloadSource::ORDER);
        } else {
            $reportItem->setDownloadSource(DownloadSource::OTHER);
        }
        $reportItem->setCustomerId($this->customerSession->getCustomerId());

        $reportItem->save();
    }
}
