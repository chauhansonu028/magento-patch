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
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\SourceOptions\UrlType;
use Magento\Framework\Url;

class FrontendUrlGenerator
{
    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Url $urlBuilder,
        ConfigProvider $configProvider
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
    }

    public function addUrl(FileInterface $file, array $params): void
    {
        $extraUrlParams = !empty($params[RegistryConstants::EXTRA_URL_PARAMS])
            ? $params[RegistryConstants::EXTRA_URL_PARAMS]
            : [];

        $file->setFrontendUrl(
            $this->urlBuilder->setScope((int)$params[RegistryConstants::STORE])->getUrl(
                'amfile/file/download',
                array_merge([
                    'file' => $this->configProvider->getUrlType() === UrlType::ID ? $file->getFileId()
                        : $file->getUrlHash(),
                    '_nosid' => true,
                ], $extraUrlParams)
            )
        );
    }
}
