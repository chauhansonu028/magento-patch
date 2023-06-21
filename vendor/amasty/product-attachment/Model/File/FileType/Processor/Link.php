<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileType\Processor;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Model\File\FileType\FrontendUrlGenerator;
use Amasty\ProductAttachment\Model\File\FileType\InvalidLinkFactory;
use Amasty\ProductAttachment\Model\File\ResourceModel\CollectionFactory as AttachmentsCollectionFactory;
use Amasty\ProductAttachment\Model\Icon\ResourceModel\Icon;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;
use GuzzleHttp\Client as GuzzleHttpClient;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\DownloadFactory as DownloadHelperFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Response;
use Psr\Log\LoggerInterface;

class Link implements TypeProcessorInterface
{
    /**
     * @var DownloadHelperFactory
     */
    private $downloadHelperFactory;

    /**
     * @var Icon
     */
    private $iconResource;

    /**
     * @var FrontendUrlGenerator
     */
    private $frontendUrl;

    /**
     * @var InvalidLinkFactory
     */
    private $invalidLinkFactory;

    /**
     * @var AttachmentsCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GuzzleHttpClient
     */
    private $guzzleHttpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DownloadHelperFactory $downloadHelperFactory,
        Icon $iconResource,
        FrontendUrlGenerator $frontendUrl,
        InvalidLinkFactory $invalidLinkFactory,
        AttachmentsCollectionFactory $collectionFactory,
        GuzzleHttpClient $guzzleHttpClient,
        LoggerInterface $logger
    ) {
        $this->downloadHelperFactory = $downloadHelperFactory;
        $this->iconResource = $iconResource;
        $this->frontendUrl = $frontendUrl;
        $this->invalidLinkFactory = $invalidLinkFactory;
        $this->collectionFactory = $collectionFactory;
        $this->guzzleHttpClient = $guzzleHttpClient;
        $this->logger = $logger;
    }

    public function addFrontendUrl(FileInterface $file, array $params): void
    {
        $this->frontendUrl->addUrl($file, $params);
    }

    public function updateFile(FileInterface $file, bool $checkExtension): FileInterface
    {
        // Attachment may not have link while mass import for multiple stores.
        if (!$file->getLink()) {
            return $file;
        }
        $data = $file->getData();
        $downloadHelper = $this->downloadHelperFactory->create();
        $downloadHelper->setResource($file->getLink(), DownloadHelper::LINK_TYPE_URL);
        try {
            $fileName = trim($downloadHelper->getFilename(), '"');
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            if (!in_array($extension, $this->iconResource->getAllowedExtensions()) && $checkExtension) {
                throw new LocalizedException(__('Disallowed Extension'));
            }
            $data[FileInterface::EXTENSION] = $extension;
            $data[FileInterface::SIZE] = $downloadHelper->getFileSize();
            $data[FileInterface::MIME_TYPE] = $downloadHelper->getContentType();

            $file->addData($data);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save new file. Error: %1', $e->getMessage()));
        }

        return $file;
    }

    public function addFileType(array &$file): void
    {
        $file[FileInterface::ATTACHMENT_TYPE] = AttachmentType::LINK;
    }

    public function collectInvalidLinks(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(FileInterface::ATTACHMENT_TYPE, AttachmentType::LINK);
        $invalidLinks = [];

        foreach ($collection->getItems() as $item) {
            try {
                $response = $this->guzzleHttpClient->get($item->getLink());
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }

            if (isset($response) && (int)$response->getStatusCode() !== Response::HTTP_OK) {
                $invalidLink = $this->invalidLinkFactory->create();
                $invalidLink->setId((int)$item->getId());
                $invalidLink->setUrl($item->getLink());
                $invalidLink->setResponse($response->getStatusCode() . ': ' . $response->getReasonPhrase());
                $invalidLinks[] = $invalidLink;
            }
        }

        return $invalidLinks;
    }
}
