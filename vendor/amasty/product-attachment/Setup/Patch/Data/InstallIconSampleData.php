<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Setup\Patch\Data;

use Amasty\Base\Helper\Deploy;
use Amasty\ProductAttachment\Model\Icon\Icon;
use Amasty\ProductAttachment\Model\Icon\IconFactory;
use Amasty\ProductAttachment\Model\Icon\Repository;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class InstallIconSampleData implements DataPatchInterface
{
    public const DEPLOY_DIR = 'pub';

    public const FILE_TYPE_ICONS = [
        'Document' => [
            'filename' => 'Document.png',
            'extensions' => [
                'doc',
                'docx',
                'txt',
                'rtf',
                'pdf',
                'djvu'
            ]
        ],
        'Image' => [
            'filename' => 'Image.png',
            'extensions' => [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'bmp'
            ]
        ],
        'Video' => [
            'filename' => 'Video.png',
            'extensions' => [
                'avi',
                'mp4'
            ]
        ],
        'Audio' => [
            'filename' => 'Audio.png',
            'extensions' => [
                'mp3',
                'jpeg',
                'ogg'
            ]
        ],
        'Archive' => [
            'filename' => 'Archive.png',
            'extensions' => [
                'zip',
                'rar',
                '7z'
            ]
        ],
        'Table' => [
            'filename' => 'Table.png',
            'extensions' => [
                'csv',
                'xls',
                'xlsx'
            ]
        ],
        'Presentation' => [
            'filename' => 'Presentation.png',
            'extensions' => [
                'pptx',
                'pptm',
                'ppt'
            ]
        ],
        'Scheme' => [
            'filename' => 'Scheme.png',
            'extensions' => [
                'ini'
            ]
        ],
        'Service' => [
            'filename' => 'Service.png',
            'extensions' => [
                'ini'
            ]
        ],
    ];

    /**
     * @var Deploy
     */
    private $deploy;

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var IconFactory
     */
    private $iconFactory;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Deploy $deploy,
        ComponentRegistrarInterface $componentRegistrar,
        Repository $repository,
        IconFactory $iconFactory,
        ResourceInterface $moduleResource,
        LoggerInterface $logger
    ) {
        $this->deploy = $deploy;
        $this->componentRegistrar = $componentRegistrar;
        $this->repository = $repository;
        $this->iconFactory = $iconFactory;
        $this->moduleResource = $moduleResource;
        $this->logger = $logger;
    }

    public function apply()
    {
        $setupDataVersion = $this->moduleResource->getDataVersion('Amasty_ProductAttachment');

        // Check if module was already installed or not.
        // If setup_version present in DB then we don't need to install fixtures, because setup_version is a marker.
        if (!$setupDataVersion) {
            $this->deploy->deployFolder(
                $this->componentRegistrar->getPath(
                    ComponentRegistrar::MODULE,
                    'Amasty_ProductAttachment'
                ) . DIRECTORY_SEPARATOR . self::DEPLOY_DIR
            );

            foreach (self::FILE_TYPE_ICONS as $type => $iconData) {
                /** @var Icon $icon */
                $icon = $this->iconFactory->create();
                $icon->setFileType($type)
                    ->setImage($iconData['filename'])
                    ->setIsActive(1)
                    ->setExtension($iconData['extensions']);

                try {
                    $this->repository->save($icon);
                } catch (CouldNotSaveException $e) {
                    $errorMessage = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
                    $this->logger->error($errorMessage);
                }
            }
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
