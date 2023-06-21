<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Healthcheck;

use Amasty\ProductAttachment\Model\ConfigProvider;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class NotificationProcessor
{
    public const TEMPLATE_ID = 'amasty_file_invalid_attachments_template';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var InvalidLinksProvider
     */
    private $invalidLinksProvider;

    /**
     * @var WebsiteResolver
     */
    private $websiteResolver;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    public function __construct(
        ConfigProvider $configProvider,
        InvalidLinksProvider $invalidLinksProvider,
        WebsiteResolver $websiteResolver,
        WebsiteRepositoryInterface $websiteRepository,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->invalidLinksProvider = $invalidLinksProvider;
        $this->websiteResolver = $websiteResolver;
        $this->websiteRepository = $websiteRepository;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    public function process(): void
    {
        if (!$invalidLinks = $this->invalidLinksProvider->getInvalidLinks()) {
            return;
        }

        foreach ($this->getMapByWebsites($invalidLinks) as $websiteId => $associatedAttachments) {
            if ($this->configProvider->isHealthcheckEnabled($websiteId)) {
                try {
                    $website = $this->websiteRepository->getById($websiteId);
                    $websiteString = $website->getName() . ' (code: ' . $website->getCode() . ')';

                    foreach ($this->configProvider->getInvalidAttachmentsNotificationRecipients(
                        $websiteId
                    ) as $recipient) {
                        $transportBuild = $this->transportBuilder->setTemplateIdentifier(self::TEMPLATE_ID)
                            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => Store::DEFAULT_STORE_ID])
                            ->setTemplateVars([
                                'website' => $websiteString,
                                'invalidAttachments' => $associatedAttachments
                            ])
                            ->setFromByScope('general', Store::DEFAULT_STORE_ID)
                            ->addTo($recipient);
                        $transportBuild->getTransport()->sendMessage();
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }

    private function getMapByWebsites(array $invalidLinks): array
    {
        $map = [];

        foreach ($invalidLinks as $invalidLink) {
            foreach ($this->websiteResolver->getAssociatedWebsites($invalidLink->getId()) as $websiteId) {
                $map[(int)$websiteId][] = $invalidLink;
            }
        }

        return $map;
    }
}
