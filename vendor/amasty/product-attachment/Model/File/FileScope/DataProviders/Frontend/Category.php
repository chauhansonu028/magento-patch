<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope\DataProviders\Frontend;

use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Model\File\Repository;
use Amasty\ProductAttachment\Model\File\FileScope\DataProviders\Category as CategoryDataProvider;

class Category implements \Amasty\ProductAttachment\Model\File\FileScope\DataProviders\FileScopeDataInterface
{
    /**
     * @var CategoryDataProvider
     */
    private $categoryDataProvider;

    /**
     * @var File
     */
    private $fileDataProvider;

    /**
     * @var Repository
     */
    private $fileRepository;

    public function __construct(
        CategoryDataProvider $categoryDataProvider,
        File $fileDataProvider,
        Repository $fileRepository
    ) {
        $this->categoryDataProvider = $categoryDataProvider;
        $this->fileDataProvider = $fileDataProvider;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute($params)
    {
        $result = [];
        if ($categoryFiles = $this->categoryDataProvider->execute($params)) {
            foreach ($categoryFiles as $categoryFile) {
                /** @var \Amasty\ProductAttachment\Model\File\File $file */
                $file = $this->fileRepository->getById($categoryFile[FileScopeInterface::FILE_ID]);
                $file->addData($categoryFile);
                if ($file = $this->fileDataProvider->processFileParams($file, $params)) {
                    $result[] = $file;
                }
            }
        }

        return $result;
    }
}
