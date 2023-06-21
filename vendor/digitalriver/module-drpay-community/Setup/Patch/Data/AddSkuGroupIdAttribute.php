<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Setup\Patch\Data;

use Digitalriver\DrPay\Model\Attribute\Source\SkuGroupId;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;

/**
 * Class AddDrSkuGroupIdAttribute
 *
 * Create SKU Group ID attribute
 */
class AddSkuGroupIdAttribute implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [CreateDigitalRiverAttributes::class];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Create SKU Group ID attribute
     *
     * @return $this
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create([['setup' => $this->moduleDataSetup]]);
        try {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'dr_sku_group_id',
                [
                    'type' => 'varchar',
                    'frontend' => '',
                    'label' => 'SKU Group',
                    'input' => 'select',
                    'class' => '',
                    'group' => 'Digital River',
                    'source' => SkuGroupId::class,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $this->moduleDataSetup->endSetup();
        return $this;
    }
}
