<?php
/**
 * Patch to create Digital River Product Attributes
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Setup\Patch\Data;

use Digitalriver\DrPay\Model\Attribute\Backend\CountryOfOrigin as CountryOfOriginBackend;
use Digitalriver\DrPay\Model\Attribute\Backend\EccnCode as EccnCodeBackend;
use Digitalriver\DrPay\Model\Attribute\Backend\HsCode;
use Digitalriver\DrPay\Model\Attribute\Source\CountryOfOrigin;
use Digitalriver\DrPay\Model\Attribute\Source\EccnCode;
use Digitalriver\DrPay\Model\Attribute\Source\TaxGroup;
use Digitalriver\DrPay\Model\Attribute\Source\TaxType;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class Create Digital River Attributes Patch
 */
class CreateDigitalRiverAttributes implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Creates dr_eccn_code, dr_hs_code, dr_country_of_origin, dr_tax_code, dr_tax group product attributes
     *
     */
    public function apply(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dr_eccn_code');
        $eavSetup->addAttribute(Product::ENTITY, 'dr_eccn_code', [
            'required' => 0,
            'type' => 'varchar',
            'label' => 'ECCN Code',
            'input' => 'select',
            'used_in_product_listing' => 0,
            'is_user_defined' => 1,
            'visible_on_front' => 0,
            'group' => 'Digital River',
            'is_filterable_in_grid' => 1,
            'is_used_in_grid' => 1,
            'source' => EccnCode::class,
            'backend' => EccnCodeBackend::class,
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dr_tax_group');
        $eavSetup->addAttribute(Product::ENTITY, 'dr_tax_group', [
            'group' => 'Digital River',
            'type' => 'varchar',
            'label' => 'Tax Group',
            'input' => 'select',
            'source' => TaxGroup::class,
            'visible' => 1,
            'required' => 0,
            'is_user_defined' => 1,
            'visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'is_filterable_in_grid' => 1,
            'is_used_in_grid' => 1
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dr_tax_type');
        $eavSetup->addAttribute(Product::ENTITY, 'dr_tax_type', [
            'group' => 'Digital River',
            'type' => 'varchar',
            'label' => 'Tax Type',
            'input' => 'select',
            'source' => TaxType::class,
            'visible' => 1,
            'required' => 0,
            'is_user_defined' => 1,
            'visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'is_filterable_in_grid' => 1,
            'is_used_in_grid' => 1
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dr_country_of_origin');
        $eavSetup->addAttribute(Product::ENTITY, 'dr_country_of_origin', [
            'required' => 0,
            'type' => 'varchar',
            'label' => 'Country of Origin',
            'input' => 'select',
            'used_in_product_listing' => 0,
            'is_user_defined' => 1,
            'visible_on_front' => 0,
            'group' => 'Digital River',
            'is_filterable_in_grid' => 1,
            'is_used_in_grid' => 1,
            'source' => CountryOfOrigin::class,
            'backend' => CountryOfOriginBackend::class,
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'dr_hs_code');
        $eavSetup->addAttribute(Product::ENTITY, 'dr_hs_code', [
            'required' => 0,
            'type' => 'text',
            'label' => 'HS Code',
            'input' => 'text',
            'used_in_product_listing' => 0,
            'is_user_defined' => 1,
            'visible_on_front' => 0,
            'group' => 'Digital River',
            'is_filterable_in_grid' => 1,
            'is_used_in_grid' => 1,
            'backend' => HsCode::class,
            'note' => 'HS Code Format XXXX.XX[.XXXX]'
        ]);
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() :array
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases() :array
    {
        return [];
    }
}
