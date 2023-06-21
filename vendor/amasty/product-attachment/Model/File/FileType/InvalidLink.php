<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileType;

class InvalidLink
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string
     */
    private $response;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
