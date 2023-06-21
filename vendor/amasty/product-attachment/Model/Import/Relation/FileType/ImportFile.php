<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Import\Relation\FileType;

use Magento\Framework\DataObject;

class ImportFile extends DataObject
{
    public const FILE_NEW = 'file_new';
    public const ATTACHMENT_TYPE = 'attachment_type';
    public const CUSTOMER_GROUPS = 'customer_groups';
    public const FILEPATH = 'filepath';
    public const LINK = 'link';
    public const FILE_ID = 'file_id';
    public const SHOW_FILE_ID = 'show_file_id';
    public const IMPORT_ID = 'import_id';
    public const EXTENSION = 'extension';
    public const LABEL = 'label';
    public const FILENAME = 'filename';
    public const INCLUDE_IN_ORDER = 'include_in_order';
    public const IS_VISIBLE = 'is_visible';

    public function setIsFileNew(bool $flag): void
    {
        $this->setData(self::FILE_NEW, $flag);
    }

    public function isFileNew(): bool
    {
        return $this->getData(self::FILE_NEW);
    }

    public function setFileType(int $type): void
    {
        $this->setData(self::ATTACHMENT_TYPE, $type);
    }

    public function getFileType(): int
    {
        return $this->getData(self::ATTACHMENT_TYPE);
    }

    public function setCustomerGroups(string $groups): void
    {
        $this->setData(self::CUSTOMER_GROUPS, $groups);
    }

    public function getCustomerGroups(): string
    {
        return $this->getData(self::CUSTOMER_GROUPS);
    }

    public function setFilePath(string $path): void
    {
        $this->setData(self::FILEPATH, $path);
    }

    public function getFilePath(): string
    {
        return $this->getData(self::FILEPATH);
    }

    public function setFileLink(string $link): void
    {
        $this->setData(self::LINK, $link);
    }

    public function getFileLink(): string
    {
        return $this->getData(self::LINK);
    }

    public function setFileId(int $id): void
    {
        $this->setData(self::FILE_ID, $id);
    }

    public function getFileId(): ?int
    {
        return $this->getData(self::FILE_ID);
    }

    public function setShowFileId(int $showFileId): void
    {
        $this->setData(self::SHOW_FILE_ID, $showFileId);
    }

    public function getShowFileId(): int
    {
        return $this->getData(self::SHOW_FILE_ID);
    }

    public function setImportId(int $importId): void
    {
        $this->setData(self::IMPORT_ID, $importId);
    }

    public function getImportId(): int
    {
        return $this->getData(self::IMPORT_ID);
    }

    public function setExtension(string $extension): void
    {
        $this->setData(self::EXTENSION, $extension);
    }

    public function getExtension(): string
    {
        return $this->getData(self::EXTENSION);
    }

    public function setLabel(string $label): void
    {
        $this->setData(self::LABEL, $label);
    }

    public function getLabel(): string
    {
        return $this->getData(self::LABEL);
    }

    public function setFilename(string $filename): void
    {
        $this->setData(self::FILENAME, $filename);
    }

    public function getFilename(): string
    {
        return $this->getData(self::FILENAME);
    }

    public function setIncludeInOrder(string $includeInOrder): void
    {
        $this->setData(self::INCLUDE_IN_ORDER, $includeInOrder);
    }

    public function getIncludeInOrder(): string
    {
        return $this->getData(self::INCLUDE_IN_ORDER);
    }

    public function setIsVisible(string $isVisible): void
    {
        $this->setData(self::IS_VISIBLE, $isVisible);
    }

    public function getIsVisible(): string
    {
        return $this->getData(self::IS_VISIBLE);
    }
}
