<?php
/**
 * Populates dr_tax_table
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class Adds Tax Code
 */
class AddDrTaxCodes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * AddDrTaxCodes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Adds Tax details to dr_tax_table table
     *
     */
    public function apply(): void
    {
        $data = [
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'Digital Image',
                'dr_sabrixcode' => '4512.100',
            ],
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'Virtual Goods',
                'dr_sabrixcode' => '55111509.12',
            ],
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'Music',
                'dr_sabrixcode' => '55111512.100',
            ],
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'Electronic Newspapers (Includes Subscriptions)',
                'dr_sabrixcode' => '55111507.120',
            ],
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'Electronic Magazines (Includes Subscriptions)',
                'dr_sabrixcode' => '55111506.120',
            ],
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'Educational / Vocational Texts',
                'dr_sabrixcode' => '55111513.120',
            ],
            [
                'dr_tax_group' => 'Downloadable Goods (Non-Software)',
                'dr_tax_type' => 'eBooks',
                'dr_sabrixcode' => '55111502.120',
            ],
            [
                'dr_tax_group' => 'Food Beverage & Household',
                'dr_tax_type' => 'Non-Prescription Vitamins',
                'dr_sabrixcode' => '51191905',
            ],
            [
                'dr_tax_group' => 'Food Beverage & Household',
                'dr_tax_type' => 'Non-Prescription Drugs',
                'dr_sabrixcode' => '5124',
            ],
            [
                'dr_tax_group' => 'Food Beverage & Household',
                'dr_tax_type' => 'Miscellaneous Supplies',
                'dr_sabrixcode' => '47',
            ],
            [
                'dr_tax_group' => 'Food Beverage & Household',
                'dr_tax_type' => 'Food - General',
                'dr_sabrixcode' => '50',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Automatic Blood Pressure Monitors',
                'dr_sabrixcode' => '531316.150',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Stove',
                'dr_sabrixcode' => '52141545.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Dehumidifier',
                'dr_sabrixcode' => '4010.200',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Air Conditioner',
                'dr_sabrixcode' => '4010.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Ceiling Fan',
                'dr_sabrixcode' => '40101609.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Light Bulbs',
                'dr_sabrixcode' => '39101629.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Freezer',
                'dr_sabrixcode' => '52141506.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Refrigerator',
                'dr_sabrixcode' => '52141501.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Washer',
                'dr_sabrixcode' => '52141601.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Dryer',
                'dr_sabrixcode' => '52141602.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Energy Star - Dishwasher',
                'dr_sabrixcode' => '52141505.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Computer Supplies - Subscription',
                'dr_sabrixcode' => '4321_S',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Mobile Devices',
                'dr_sabrixcode' => '43211509',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Medical Equipment',
                'dr_sabrixcode' => '42',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Consumer Electronics (Photographic, Filming, or Video Equipment)',
                'dr_sabrixcode' => '4512',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Consumer Electronics (T.V., Monitor, Display) - Size (>4"<15")',
                'dr_sabrixcode' => '52161500_C',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Consumer Electronics (T.V., Monitor, Display) - Size (= or >35")',
                'dr_sabrixcode' => '52161500_B',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Consumer Electronics (T.V., Monitor, Display) - Size (= or >15"<35")',
                'dr_sabrixcode' => '52161500_A',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Video Game Consoles and Accessories',
                'dr_sabrixcode' => '601410',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Uniforms',
                'dr_sabrixcode' => '531027',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Sports and Recreation Equipment',
                'dr_sabrixcode' => '49',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'School Supplies',
                'dr_sabrixcode' => '441216',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'School Instructional Materials',
                'dr_sabrixcode' => '6010',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Safety Clothing (Not Suitable for Everyday Use)',
                'dr_sabrixcode' => '461815.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Printed Media (Non-Subscription)',
                'dr_sabrixcode' => '5510',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Newspapers (Includes Subscription)',
                'dr_sabrixcode' => '55101504.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Magazines (Includes Subscriptions)',
                'dr_sabrixcode' => '55101506.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'General Merchandise',
                'dr_sabrixcode' => '601410',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Consumer Electronics (Non-Computer)',
                'dr_sabrixcode' => '5216',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Computers',
                'dr_sabrixcode' => '4321_A',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Computer Supplies',
                'dr_sabrixcode' => '4321_C',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Computer Peripheral Devices',
                'dr_sabrixcode' => '4321_B',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Books - General Purpose',
                'dr_sabrixcode' => '55101510',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Books - Educational and Vocational Texts',
                'dr_sabrixcode' => '55101509',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Backpacks',
                'dr_sabrixcode' => '53121603',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Apparel & Footwear (Everyday Use)',
                'dr_sabrixcode' => '531029.100',
            ],
            [
                'dr_tax_group' => 'Physical Goods',
                'dr_tax_type' => 'Apparel & Footwear (Athletic Use)',
                'dr_sabrixcode' => '531029.200',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Installation Service Charges Provided by the Seller of TPP',
                'dr_sabrixcode' => '70.360',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Mandatory Maintenance Agreements - Services and Upgrades, Only for Downloadable',
                'dr_sabrixcode' => '81112201.121',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Computer Services',
                'dr_sabrixcode' => '8111',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Consulting Services',
                'dr_sabrixcode' => '70.60',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'General Services',
                'dr_sabrixcode' => '70.100',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Gift Cards',
                'dr_sabrixcode' => '70.120',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Mandatory Maintenance Agreements - Services and Upgrades,' .
                    ' for Downloadable and Physical Products',
                'dr_sabrixcode' => '81112201.120',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Non-warranty Repairs',
                'dr_sabrixcode' => '70.150',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Online Data Storage',
                'dr_sabrixcode' => '811121',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Optional Maintenance Agreements - Services and Upgrades, for Downloadable Products',
                'dr_sabrixcode' => '81112201.420',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Optional Maintenance Agreements - Services and Upgrades, for Physical Products',
                'dr_sabrixcode' => '81112201.410',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Optional Maintenance Agreements - Services Only, for Downloadable and' .
                    ' Physical Products',
                'dr_sabrixcode' => '81112201.200',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Seminar Classes',
                'dr_sabrixcode' => '70.300',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Software Training',
                'dr_sabrixcode' => '70.280',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Technical Support',
                'dr_sabrixcode' => '811118.100',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Virtual Currency',
                'dr_sabrixcode' => '70.120_A',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Membership Dues - General',
                'dr_sabrixcode' => '70.220',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Membership Dues & Professional Organization',
                'dr_sabrixcode' => '70.222',
            ],
            [
                'dr_tax_group' => 'Services & Miscellaneous',
                'dr_tax_type' => 'Web Hosting',
                'dr_sabrixcode' => '81112105',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Backup Media (CD/DVD) - One Disc per Order',
                'dr_sabrixcode' => '4323.310_B',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Software as a Service',
                'dr_sabrixcode' => '81112106',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Physical Software (Gaming Only)',
                'dr_sabrixcode' => '4323.310_E',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Physical Software (Non-Gaming)',
                'dr_sabrixcode' => '4323.310_A',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Physical Media Kits',
                'dr_sabrixcode' => '4323.310_D',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Backup Media (CD/DVD) - One Disc per Product',
                'dr_sabrixcode' => '4323.310_C',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Downloadable Media Kits',
                'dr_sabrixcode' => '4323.320_C',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Downloadable Software (Gaming Only)',
                'dr_sabrixcode' => '4323.320_D',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Downloadable Software (Non-Gaming, Includes Software Subscriptions)',
                'dr_sabrixcode' => '4323.320_A',
            ],
            [
                'dr_tax_group' => 'Software (Downloadable & Physical)',
                'dr_tax_type' => 'Extended Download Service',
                'dr_sabrixcode' => '4323.320_B',
            ],
            [
                'dr_tax_group' => 'Warranties',
                'dr_tax_type' => 'Optional Warranties - Purchased at Time of Sale of for Consumer Goods, Labor Only',
                'dr_sabrixcode' => '95.210',
            ],
            [
                'dr_tax_group' => 'Warranties',
                'dr_tax_type' => 'Optional Warranties - NOT Purchased at Time of Sale of for Consumer Goods,' .
                    ' Parts & Labor',
                'dr_sabrixcode' => '95.222',
            ],
            [
                'dr_tax_group' => 'Warranties',
                'dr_tax_type' => 'Optional Warranties - NOT Purchased at Time of Sale of for Consumer Goods,' .
                    ' Labor Only',
                'dr_sabrixcode' => '95.220',
            ],
            [
                'dr_tax_group' => 'Warranties',
                'dr_tax_type' => 'Mandatory Warranties',
                'dr_sabrixcode' => '95.100',
            ],
            [
                'dr_tax_group' => 'Warranties',
                'dr_tax_type' => 'Optional Warranties - Purchased at Time of Sale of for Consumer Goods, Parts & Labor',
                'dr_sabrixcode' => '95.212',
            ]
        ];

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('dr_tax_table'),
            $data
        );
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
