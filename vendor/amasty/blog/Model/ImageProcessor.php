<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\ImageFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ImageProcessor
{
    public const BLOG_MEDIA_PATH = 'amasty/blog';

    public const BLOG_MEDIA_TMP_PATH = 'amasty/blog/tmp';

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $ioFile;

    /**
     * @var Database
     */
    private $coreFileStorageDatabase;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Blog\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Filesystem $filesystem,
        ImageUploader $imageUploader,
        ImageFactory $imageFactory,
        StoreManagerInterface $storeManager,
        File $ioFile,
        Database $coreFileStorageDatabase,
        LoggerInterface $logger,
        \Amasty\Blog\Model\ConfigProvider $configProvider
    ) {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->ioFile = $ioFile;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
    }

    /**
     * @return WriteInterface
     * @throws FileSystemException
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }

    /**
     * @param $imageName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getThumbnailUrl($imageName)
    {
        $pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        if ($pubDirectory->isExist($imageName)) {
            $result = $this->storeManager->getStore()->getBaseUrl() . trim($imageName, '/');
        } else {
            $result = $this->getCategoryIconMedia(self::BLOG_MEDIA_PATH) . '/' . $imageName;
        }

        return $result;
    }

    /**
     * @param string $iconName
     *
     * @return string
     */
    private function getImageRelativePath($iconName)
    {
        return self::BLOG_MEDIA_PATH . DIRECTORY_SEPARATOR . $iconName;
    }

    /**
     * @param $mediaPath
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryIconMedia($mediaPath)
    {
        return $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $mediaPath;
    }

    /**
     * @param $iconName
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processCategoryIcon($iconName)
    {
        $this->imageUploader->moveFileFromTmp($iconName, true);

        $filename = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($iconName));
        try {
            /** @var \Magento\Framework\Image $imageProcessor */
            $imageProcessor = $this->imageFactory->create(['fileName' => $filename]);
            $imageProcessor->keepAspectRatio(true);
            $imageProcessor->keepFrame(true);
            $imageProcessor->keepTransparency(true);
            $imageProcessor->backgroundColor([255, 255, 255]);
            $imageProcessor->quality($this->configProvider->getImageQuality());
            $imageProcessor->save();
        } catch (\Exception $e) {
            null;// Unsupported image format.
        }
    }

    public function moveFile(array $images): ?string
    {
        $filePath = null;
        if (count($images) > 0) {
            foreach ($images as $image) {
                if (array_key_exists('file', $image)) {
                    $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    if ($mediaDirectory->isExist(self::BLOG_MEDIA_TMP_PATH . '/' . $image['file'])) {
                        $filePath = $this->moveFileFromTmp($image['file']);
                        break;
                    }
                } elseif (isset($image['type'])) {
                    $filePath = $image['url'] ?? '';
                }
            }
        }

        return $filePath;
    }

    /**
     * @param $iconName
     * @throws FileSystemException
     */
    public function deleteImage($iconName)
    {
        $this->getMediaDirectory()->delete($this->getImageRelativePath($iconName));
    }

    /**
     * @param $imageName
     *
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function copy($imageName)
    {
        $basePath = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));
        $imageName = explode('.', $imageName);
        $imageName[0] .= '-' . random_int(1, 1000);
        $imageName = implode('.', $imageName);
        $newPath = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));

        try {
            $this->ioFile->cp(
                $basePath,
                $newPath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $imageName;
    }

    /**
     * for fix SaveBaseCategoryImageInformation Plugin on ce
     */
    public function moveFileFromTmp($imageName, $returnRelativePath = false): string
    {
        $baseTmpPath = $this->imageUploader->getBaseTmpPath();
        $basePath = $this->imageUploader->getBasePath();

        $baseImagePath = $this->imageUploader->getFilePath(
            $basePath,
            Uploader::getNewFileName(
                $this->getMediaDirectory()->getAbsolutePath($this->imageUploader->getFilePath($basePath, $imageName))
            )
        );
        $baseTmpImagePath = $this->imageUploader->getFilePath($baseTmpPath, $imageName);

        try {
            $this->coreFileStorageDatabase->copyFile($baseTmpImagePath, $baseImagePath);
            $this->getMediaDirectory()->renameFile($baseTmpImagePath, $baseImagePath);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).'),
                $e
            );
        }

        return $returnRelativePath ? $baseImagePath : $imageName;
    }
}
