<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RemoveSocialNetworkStumbleupon implements DataPatchInterface
{
    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(ConfigInterface $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $networkName = 'stumbleupon';
        $configPath = 'amblog/social/networks';
        $connection = $this->resourceConfig->getConnection();
        $configTable = 'core_config_data';
        $select = $connection->select()->from($this->resourceConfig->getTable($configTable))
            ->where('path = ?', $configPath);

        foreach ($connection->fetchAll($select) as $config) {
            $networks = explode(',', $config['value']);
            if (in_array($networkName, $networks)) {
                $networkKey = array_search($networkName, $networks);
                unset($networks[$networkKey]);
                $connection->update(
                    $this->resourceConfig->getTable($configTable),
                    [
                        'value' => implode(',', $networks),
                    ],
                    [
                        'config_id = ?' => $config['config_id']
                    ]
                );
            }
        }

        return $this;
    }
}
