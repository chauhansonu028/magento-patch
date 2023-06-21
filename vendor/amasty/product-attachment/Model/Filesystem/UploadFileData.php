<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Filesystem;

class UploadFileData
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $tmpFileName;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var int
     */
    private $fileSize;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getTmpFileName()
    {
        return $this->tmpFileName;
    }

    /**
     * @param string $tmpFileName
     */
    public function setTmpFileName($tmpFileName)
    {
        $this->tmpFileName = $tmpFileName;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return (int)$this->fileSize;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = (int)$fileSize;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }
}
