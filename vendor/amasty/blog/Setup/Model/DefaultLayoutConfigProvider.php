<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Model;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Io\File;

class DefaultLayoutConfigProvider
{
    const DEFAULT_LAYOUT_CONFIG_PATH = 'data/layout';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        Reader $reader,
        File $file
    ) {
        $this->reader = $reader;
        $this->file = $file;
    }

    public function getDefaultConfig(string $configPath): string
    {
        $fileContent = '{}';
        $configPathParts = explode('/', $configPath);
        $layoutConfigName = sprintf('%s.json', end($configPathParts));
        $configFilePath = $this->getFilePath($layoutConfigName);

        if ($this->file->fileExists($configFilePath)) {
            $fileContent = $this->file->read($configFilePath);
        }

        return trim($fileContent);
    }

    private function getFilePath(string $fileName): string
    {
        $moduleDir = $this->reader->getModuleDir('', 'Amasty_Blog');

        return $moduleDir . DIRECTORY_SEPARATOR . self::DEFAULT_LAYOUT_CONFIG_PATH . DIRECTORY_SEPARATOR . $fileName;
    }
}
