<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileType;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileFactory;

class AddFileType
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var TypeProcessorProvider
     */
    private $typeProcessorProvider;

    public function __construct(
        FileFactory $fileFactory,
        TypeProcessorProvider $typeProcessorProvider
    ) {
        $this->fileFactory = $fileFactory;
        $this->typeProcessorProvider = $typeProcessorProvider;
    }

    /**
     * @param array $file - added file data on product or product category page.
     * @param array $params - category or product id, store id and file data.
     *
     * @return FileInterface|null
     */
    public function addType(array $file, array $params): ?FileInterface
    {
        $newFile = null;
        $typeCodes = $this->typeProcessorProvider->getTypeCodes();
        foreach ($typeCodes as $type => $typeCode) {
            if (!empty($file[$typeCode])) {
                $typeProcessor = $this->typeProcessorProvider->getProcessorByType($type);
                $typeProcessor->addFileType($file);
                if (isset($params[RegistryConstants::CATEGORY])) {
                    $file[FileInterface::CATEGORIES] = [$params[RegistryConstants::CATEGORY]];
                }
                if (isset($params[RegistryConstants::PRODUCT])) {
                    $file[FileInterface::PRODUCTS] = [$params[RegistryConstants::PRODUCT]];
                }
                $file[FileInterface::FILE_ID] = null;

                $newFile = $this->fileFactory->create();
                $newFile->addData($file);
                break;
            }
        }

        return $newFile;
    }
}
