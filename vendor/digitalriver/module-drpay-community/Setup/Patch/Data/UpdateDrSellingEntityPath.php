<?php
/**
 * Patch to update DR selling entity setting path
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class Adds Countries
 */
class UpdateDrSellingEntityPath implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * UpdateDrSellingEntityPath constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * update setting at core_config_data table
     *
     */
    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');
        $whereCondition = "path = 'dr_settings/stored_methods/default_selling_entity'";
        $select = $connection->select()->from(
            $table,
            'config_id'
        )->where(
            $whereCondition
        )->limit(
            1
        );
        if ($connection->fetchRow($select)) {
            $connection->update(
                $table,
                ['path' => 'dr_settings/config/default_selling_entity'],
                $whereCondition
            );
        }
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
