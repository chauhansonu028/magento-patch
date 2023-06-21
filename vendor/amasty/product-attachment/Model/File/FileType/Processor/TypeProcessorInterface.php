<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileType\Processor;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Model\File\FileType\InvalidLink;

interface TypeProcessorInterface
{
    /**
     * @param FileInterface $file
     * @param array $params
     *
     * @return void
     */
    public function addFrontendUrl(FileInterface $file, array $params): void;

    /**
     * @param FileInterface $file
     * @param bool $checkExtension
     *
     * @return FileInterface
     */
    public function updateFile(FileInterface $file, bool $checkExtension): FileInterface;

    /**
     * @param array $file
     *
     * @return void
     */
    public function addFileType(array &$file): void;

    /**
     * @return InvalidLink[]
     */
    public function collectInvalidLinks(): array;
}
