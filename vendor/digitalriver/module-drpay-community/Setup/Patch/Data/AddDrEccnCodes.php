<?php
/**
 * Patch to add Digital River ECCN code
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class Eccn Codes Patch
 */
class AddDrEccnCodes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * AddDrCountries constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Adds Country details to dr_eccn_code table
     *
     */
    public function apply(): void
    {
        $data = [
            [
                'classification_code' => '1A999',
                'description' => '1A999 Specific processing equipment, n.e.s., as follows: a. Radiation detection,' .
                    ' monitoring and measurement equipment, n.e.s.; b. Radiographic detection equipment such as x-ray' .
                    ' converters, and storage phosphor image plates',
                'notes' => ''
            ],
            [
                'classification_code' => '3A991',
                'description' => 'Electronic Devices and Components not controlled by 3A001.',
                'notes' => 'Note: Items classified under the sub-heading 3A991.a.1 are subject to increased licensing' .
                    ' requirements and cannot be resold by Digital River.'
            ],
            [
                'classification_code' => '3A992',
                'description' => 'General Purpose electronic equipment not controlled by 3A002.',
                'notes' => ''
            ],
            [
                'classification_code' => '3A999',
                'description' => 'Specific Processing Equipment, (Frequency Changers, Mass Spectrometers, flash x-ray' .
                    ' machines, pulse amplifiers, electronic equipment for time delay generation or time interval' .
                    ' measurement)',
                'notes' => ''
            ],
            [
                'classification_code'=>'3B991',
                'description'=>'Equipment not controlled by 3B001 for the Manufacture of Electronic Components and' .
                    ' Materials, and Specially Designed Components and Accessories Therefor',
                'notes'=>''
            ],
            [
                "classification_code"=>"3B992",
                "description"=>"Equipment not controlled by 3B002 for the Inspection or testing of electronic" .
                    " components and materials, and specially designed components and accessories therefor",
                "notes"=>""
            ],
            [
                "classification_code"=>"3C992",
                "description"=>"Positive resists designed for semiconductor lithography specially adjusted " .
                    "(optimized) for use at wavelengths between 370 and 245 nm.",
                "notes"=>""
            ],
            [
                "classification_code"=>"3D991",
                "description"=>"\"Software\" specially designed for the \"development\", \"production\", or" .
                    " \"use\" of electronic devices or components controlled by 3A991, general purpose electronic" .
                    " equipment controlled by 3A992, or manufacturing and test equipment controlled by 3B991 and" .
                    " 3B992; or \"software\" specially designed for the \"use\" of equipment controlled by" .
                    " 3B001.g and .h.",
                "notes"=>""
            ],
            [
                "classification_code"=>"3E991",
                "description"=>"\"Technology\" for the \"development\", \"production\", or \"use\" of electronic" .
                    " devices or components controlled by 3A991, general purpose electronic equipment controlled by" .
                    " 3A992, or manufacturing and test equipment controlled by 3B991 or 3B992, or materials" .
                    " controlled by 3C992.",
                "notes"=>""
            ],
            [
                "classification_code"=>"4A994",
                "description"=>"Computers, \"electronic assemblies\", and related equipment not controlled by" .
                    " 4A001, 4A002, or 4A003, and specially designed components therefor.",
                "notes"=>""
            ],
            [
                "classification_code"=>"4D993",
                "description"=>"\"Program\" proof and validation \"software\", \"software\" allowing the automatic" .
                    " generation of \"source codes\", and operating system \"software\" that are specially designed" .
                    " for real time processing equipment.",
                "notes"=>""
            ],
            [
                "classification_code"=>"4D994",
                "description"=>"\"Software\" other than that controlled in 4D001 specially designed or modified" .
                    " for the \"development\", \"production\", or \"use\" of equipment controlled by" .
                    " 4A101, 4A994, 4B994, and materials controlled by 4C994.",
                "notes"=>""
            ],
            [
                "classification_code"=>"4E992",
                "description"=>"\"Technology\" other than that controlled in 4E001 for the" .
                    " \"development,\" \"production,\" or \"use\" of equipment controlled" .
                    " by 4A994, or \"software\" controlled by 4D993 or 4D994",
                "notes"=>""
            ],
            [
                "classification_code"=>"4E993",
                "description"=>"\"Technology\" for the \"development\" or \"production\" of equipment designed" .
                    " for \"multi-data-stream processing.\"",
                "notes"=>""
            ],
            [
                "classification_code"=>"5A002",
                "description"=>"\"Information security\" systems, equipment and components therefor, as follows" .
                    " (see List of Items Controlled).",
                "notes"=>"Note: Items classified under this ECCN must also be eligible for license" .
                    " exception \"ENC\" or cannot be resold by Digital River."
            ],
            [
                "classification_code"=>"5A991",
                "description"=>"Telecommunication Equipment, Not Controlled by 5A001.",
                "notes"=>""
            ],
            [
                "classification_code"=>"5A992",
                "description"=>"Equipment not controlled by 5A002 Items classified under this ECCN must also be" .
                    " eligible for license exception \"ENC\" or cannot be resold by Digital River",
                "notes"=>""
            ],
            [
                "classification_code"=>"5B991",
                "description"=>"Telecommunications test equipment",
                "notes"=>""
            ],
            [
                "classification_code"=>"5C991",
                "description"=>"Preforms of glass or of any other material optimized for the manufacture of optical" .
                    " fibers controlled by 5A991.",
                "notes"=>""
            ],
            [
                "classification_code"=>"5D002",
                "description"=>"Encrypted \"Software\".",
                "notes"=>"Note: Items classified under this ECCN must also be eligible for license" .
                    " exception \"ENC\" or cannot be resold by Digital River"
            ],
            [
                "classification_code"=>"5D991",
                "description"=>"\"Software\" specially designed or modified for the" .
                    " \"development\", \"production\" or \"use\" of equipment controlled by 5A991 and" .
                    " 5B991, and dynamic adaptive routing software as described in the List of Items Controlled.",
                "notes"=>""
            ],
            [
                "classification_code"=>"5D992",
                "description"=>"\"Information Security\" \"Software\" not controlled by 5D002.",
                "notes"=>""
            ],
            [
                "classification_code"=>"5E991",
                "description"=>"\"Technology\" for the \"Development\", \"Production\" or \"Use\" of Equipment" .
                    " Controlled by 5A991 or 5B991, or \"Software\" Controlled by 5D991, and" .
                    " Other \"Technologies\" as Follows (see List of Items Controlled)",
                "notes"=>""
            ],
            [
                "classification_code"=>"5E992",
                "description"=>"\"Information Security\" \"technology\" according to the General Technology" .
                    " Note, not controlled by 5E002.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6A991",
                "description"=>"Marine or terrestrial acoustic equipment, n.e.s., capable of detecting or locating" .
                    " underwater objects or features or positioning surface vessels or underwater vehicles; and" .
                    " specially designed components, n.e.s.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6A993",
                "description"=>"Cameras, not controlled by 6A003 or 6A203, as" .
                    " follows (see List of Items Controlled). U.S. export license is required to" .
                    " export, reexport or transfer (in-country) products classified as" .
                    " 6A993.a to a \"Military End-User\" in any country except the U.S. or Canada.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6A994",
                "description"=>"Optics, not controlled by 6A004.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6A995",
                "description"=>"\"Lasers\" (see List of Items Controlled).",
                "notes"=>""
            ],
            [
                "classification_code"=>"6A996",
                "description"=>"\"Magnetometers\" not controlled by ECCN 6A006, \"Superconductive\" electromagnetic" .
                    " sensors, and specially designed components therefore, as follows (see List of Items Controlled).",
                "notes"=>""
            ],
            [
                "classification_code"=>"6A997",
                "description"=>"Gravity meters (gravimeters) for ground use, n.e.s.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6C992",
                "description"=>"Optical sensing fibers not controlled by 6A002.d.3 which are modified structurally" .
                    " to have a `beat length' of less than 500 mm (high birefringence) or optical sensor materials" .
                    " not described in 6C002.b and having a zinc content of equal to or more" .
                    " than 6% by `mole fraction'.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6C994",
                "description"=>"Optical materials.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6D992",
                "description"=>"\"Software\" specially designed for the \"development\" or \"production\" of" .
                    " equipment controlled by 6A992, 6A994, or 6A995.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6D993",
                "description"=>"Other \"software\" not controlled by 6D003.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6E992",
                "description"=>"\"Technology\" for the \"development\" or \"production\" of equipment, materials" .
                    " or \"software\" controlled by 6A992, 6A994, or 6A995, 6B995, 6C992, 6C994, or 6D993.",
                "notes"=>""
            ],
            [
                "classification_code"=>"6E993",
                "description"=>"Other \"technology\", not controlled by 6E003, as follows" .
                    " (see List of Items Controlled).",
                "notes"=>""
            ],
            [
                "classification_code"=>"7B994",
                "description"=>"Other equipment for the test, inspection, or \"production\" of navigation and" .
                    " avionics equipment.",
                "notes"=>""
            ],
            [
                "classification_code"=>"7D994",
                "description"=>"\"Software\", n.e.s., for the \"development\", \"production\", or \"use\" of" .
                    " navigation, airborne communication and other avionics.",
                "notes"=>""
            ],
            [
                "classification_code"=>"7E994",
                "description"=>"\"Technology\", n.e.s., for the \"development\", \"production\", or \"use\" of" .
                    " navigation, airborne communication, and other avionics equipment.",
                "notes"=>""
            ],
            [
                "classification_code"=>"8A992",
                "description"=>"Vessels, Marine Systems or Equipment, Not Controlled by 8A001, 8A002 or 8A018, and" .
                    " Specially Designed Parts Therefor",
                "notes"=>""
            ],
            [
                "classification_code"=>"8D992",
                "description"=>"\"Software\" specially designed or modified for" .
                    " the \"development\", \"production\" or \"use\" of equipment controlled by 8A992.",
                "notes"=>""
            ],
            [
                "classification_code"=>"8E992",
                "description"=>"\"Technology\" for the \"development\", \"production\" or \"use\" of equipment" .
                    " controlled by 8A992.",
                "notes"=>""
            ],
            [
                "classification_code"=>"EAR99",
                "description"=>"Subject to the Export Administration Regulations, but not controlled elsewhere on" .
                    " the Commerce Control List.",
                "notes"=>""
            ]
        ];

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('dr_eccn_code'),
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
