<?php
/**
 * Patch to add Digital River country details
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
class AddDrCountries implements DataPatchInterface
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
     * Adds Country details to dr_country_of_origin table
     *
     */
    public function apply(): void
    {
        $data = [
            ["country_code"=>"AM","country_name"=>"Armenia"],
            ["country_code"=>"AN","country_name"=>"Netherlands Antilles"],
            ["country_code"=>"AR","country_name"=>"Argentina"],
            ["country_code"=>"AS","country_name"=>"American Samoa"],
            ["country_code"=>"AT","country_name"=>"Austria"],
            ["country_code"=>"AU","country_name"=>"Australia"],
            ["country_code"=>"AX","country_name"=>"Åland Islands"],
            ["country_code"=>"AZ","country_name"=>"Azerbaijan"],
            ["country_code"=>"BB","country_name"=>"Barbados"],
            ["country_code"=>"BD","country_name"=>"Bangladesh"],
            ["country_code"=>"BE","country_name"=>"Belgium"],
            ["country_code"=>"BG","country_name"=>"Bulgaria"],
            ["country_code"=>"BH","country_name"=>"Bahrain"],
            ["country_code"=>"BJ","country_name"=>"Benin"],
            ["country_code"=>"BM","country_name"=>"Bermuda"],
            ["country_code"=>"BN","country_name"=>"Brunei"],
            ["country_code"=>"BO","country_name"=>"Bolivia"],
            ["country_code"=>"BR","country_name"=>"Brazil"],
            ["country_code"=>"BS","country_name"=>"Bahamas"],
            ["country_code"=>"BT","country_name"=>"Bhutan"],
            ["country_code"=>"BV","country_name"=>"Bouvet Island"],
            ["country_code"=>"BW","country_name"=>"Botswana"],
            ["country_code"=>"BZ","country_name"=>"Belize"],
            ["country_code"=>"CA","country_name"=>"Canada"],
            ["country_code"=>"CC","country_name"=>"Cocos Islands"],
            ["country_code"=>"CH","country_name"=>"Switzerland"],
            ["country_code"=>"CI","country_name"=>"Côte d'Ivoire"],
            ["country_code"=>"CK","country_name"=>"Cook Islands"],
            ["country_code"=>"CL","country_name"=>"Chile"],
            ["country_code"=>"CM","country_name"=>"Cameroon"],
            ["country_code"=>"CN","country_name"=>"China"],
            ["country_code"=>"CO","country_name"=>"Colombia"],
            ["country_code"=>"CR","country_name"=>"Costa Rica"],
            ["country_code"=>"CV","country_name"=>"Cape Verde"],
            ["country_code"=>"CW","country_name"=>"Curaçao"],
            ["country_code"=>"CX","country_name"=>"Christmas Island"],
            ["country_code"=>"CY","country_name"=>"Cyprus"],
            ["country_code"=>"CZ","country_name"=>"Czech Republic"],
            ["country_code"=>"DE","country_name"=>"Germany"],
            ["country_code"=>"DJ","country_name"=>"Djibouti"],
            ["country_code"=>"DK","country_name"=>"Denmark"],
            ["country_code"=>"DM","country_name"=>"Dominica"],
            ["country_code"=>"DO","country_name"=>"Dominican Republic"],
            ["country_code"=>"DZ","country_name"=>"Algeria"],
            ["country_code"=>"EC","country_name"=>"Ecuador"],
            ["country_code"=>"EE","country_name"=>"Estonia"],
            ["country_code"=>"EG","country_name"=>"Egypt"],
            ["country_code"=>"ES","country_name"=>"Spain"],
            ["country_code"=>"ET","country_name"=>"Ethiopia"],
            ["country_code"=>"FI","country_name"=>"Finland"],
            ["country_code"=>"FJ","country_name"=>"Fiji"],
            ["country_code"=>"FK","country_name"=>"Falkland Islands"],
            ["country_code"=>"FM","country_name"=>"Micronesia"],
            ["country_code"=>"FO","country_name"=>"Faroe Islands"],
            ["country_code"=>"FR","country_name"=>"France"],
            ["country_code"=>"GA","country_name"=>"Gabon"],
            ["country_code"=>"GB","country_name"=>"United Kingdom"],
            ["country_code"=>"GD","country_name"=>"Grenada"],
            ["country_code"=>"GE","country_name"=>"Georgia"],
            ["country_code"=>"GF","country_name"=>"French Guiana"],
            ["country_code"=>"GH","country_name"=>"Ghana"],
            ["country_code"=>"GI","country_name"=>"Gibraltar"],
            ["country_code"=>"GL","country_name"=>"Greenland"],
            ["country_code"=>"GM","country_name"=>"Gambia"],
            ["country_code"=>"GP","country_name"=>"Guadeloupe"],
            ["country_code"=>"GQ","country_name"=>"Equatorial Guinea"],
            ["country_code"=>"GR","country_name"=>"Greece"],
            ["country_code"=>"GS","country_name"=>"South Georgia And The South Sandwich Islands"],
            ["country_code"=>"GT","country_name"=>"Guatemala"],
            ["country_code"=>"GU","country_name"=>"Guam"],
            ["country_code"=>"GY","country_name"=>"Guyana"],
            ["country_code"=>"HK","country_name"=>"Hong Kong"],
            ["country_code"=>"HM","country_name"=>"Heard Island And McDonald Islands"],
            ["country_code"=>"HN","country_name"=>"Honduras"],
            ["country_code"=>"HT","country_name"=>"Haiti"],
            ["country_code"=>"HU","country_name"=>"Hungary"],
            ["country_code"=>"ID","country_name"=>"Indonesia"],
            ["country_code"=>"IE","country_name"=>"Ireland"],
            ["country_code"=>"IL","country_name"=>"Israel"],
            ["country_code"=>"IN","country_name"=>"India"],
            ["country_code"=>"IO","country_name"=>"British Indian Ocean Territory"],
            ["country_code"=>"IS","country_name"=>"Iceland"],
            ["country_code"=>"IT","country_name"=>"Italy"],
            ["country_code"=>"JM","country_name"=>"Jamaica"],
            ["country_code"=>"JO","country_name"=>"Jordan"],
            ["country_code"=>"JP","country_name"=>"Japan"],
            ["country_code"=>"KE","country_name"=>"Kenya"],
            ["country_code"=>"KG","country_name"=>"Kyrgyzstan"],
            ["country_code"=>"KH","country_name"=>"Cambodia"],
            ["country_code"=>"KI","country_name"=>"Kiribati"],
            ["country_code"=>"KM","country_name"=>"Comoros"],
            ["country_code"=>"KN","country_name"=>"Saint Kitts And Nevis"],
            ["country_code"=>"KR","country_name"=>"South Korea"],
            ["country_code"=>"KW","country_name"=>"Kuwait"],
            ["country_code"=>"KY","country_name"=>"Cayman Islands"],
            ["country_code"=>"KZ","country_name"=>"Kazakhstan"],
            ["country_code"=>"LA","country_name"=>"Laos"],
            ["country_code"=>"LC","country_name"=>"Saint Lucia"],
            ["country_code"=>"LI","country_name"=>"Liechtenstein"],
            ["country_code"=>"LK","country_name"=>"Sri Lanka"],
            ["country_code"=>"LS","country_name"=>"Lesotho"],
            ["country_code"=>"LT","country_name"=>"Lithuania"],
            ["country_code"=>"LU","country_name"=>"Luxembourg"],
            ["country_code"=>"LV","country_name"=>"Latvia"],
            ["country_code"=>"MA","country_name"=>"Morocco"],
            ["country_code"=>"MC","country_name"=>"Monaco"],
            ["country_code"=>"MG","country_name"=>"Madagascar"],
            ["country_code"=>"MH","country_name"=>"Marshall Islands"],
            ["country_code"=>"MN","country_name"=>"Mongolia"],
            ["country_code"=>"MO","country_name"=>"Macao"],
            ["country_code"=>"MP","country_name"=>"Northern Mariana Islands"],
            ["country_code"=>"MQ","country_name"=>"Martinique"],
            ["country_code"=>"MR","country_name"=>"Mauritania"],
            ["country_code"=>"MS","country_name"=>"Montserrat"],
            ["country_code"=>"MT","country_name"=>"Malta"],
            ["country_code"=>"MU","country_name"=>"Mauritius"],
            ["country_code"=>"MV","country_name"=>"Maldives"],
            ["country_code"=>"MW","country_name"=>"Malawi"],
            ["country_code"=>"MX","country_name"=>"Mexico"],
            ["country_code"=>"MY","country_name"=>"Malaysia"],
            ["country_code"=>"MZ","country_name"=>"Mozambique"],
            ["country_code"=>"NA","country_name"=>"Namibia"],
            ["country_code"=>"NC","country_name"=>"New Caledonia"],
            ["country_code"=>"NE","country_name"=>"Niger"],
            ["country_code"=>"NF","country_name"=>"Norfolk Island"],
            ["country_code"=>"NG","country_name"=>"Nigeria"],
            ["country_code"=>"NL","country_name"=>"Netherlands"],
            ["country_code"=>"NO","country_name"=>"Norway"],
            ["country_code"=>"NP","country_name"=>"Nepal"],
            ["country_code"=>"NR","country_name"=>"Nauru"],
            ["country_code"=>"NU","country_name"=>"Niue"],
            ["country_code"=>"NZ","country_name"=>"New Zealand"],
            ["country_code"=>"OM","country_name"=>"Oman"],
            ["country_code"=>"PA","country_name"=>"Panama"],
            ["country_code"=>"PE","country_name"=>"Peru"],
            ["country_code"=>"PF","country_name"=>"French Polynesia"],
            ["country_code"=>"PG","country_name"=>"Papua New Guinea"],
            ["country_code"=>"PH","country_name"=>"Philippines"],
            ["country_code"=>"PK","country_name"=>"Pakistan"],
            ["country_code"=>"PL","country_name"=>"Poland"],
            ["country_code"=>"PM","country_name"=>"Saint Pierre And Miquelon"],
            ["country_code"=>"PN","country_name"=>"Pitcairn"],
            ["country_code"=>"PR","country_name"=>"Puerto Rico"],
            ["country_code"=>"PT","country_name"=>"Portugal"],
            ["country_code"=>"PW","country_name"=>"Palau"],
            ["country_code"=>"PY","country_name"=>"Paraguay"],
            ["country_code"=>"QA","country_name"=>"Qatar"],
            ["country_code"=>"RE","country_name"=>"Reunion"],
            ["country_code"=>"RO","country_name"=>"Romania"],
            ["country_code"=>"SA","country_name"=>"Saudi Arabia"],
            ["country_code"=>"SB","country_name"=>"Solomon Islands"],
            ["country_code"=>"SC","country_name"=>"Seychelles"],
            ["country_code"=>"SE","country_name"=>"Sweden"],
            ["country_code"=>"SG","country_name"=>"Singapore"],
            ["country_code"=>"SH","country_name"=>"Saint Helena"],
            ["country_code"=>"SI","country_name"=>"Slovenia"],
            ["country_code"=>"SJ","country_name"=>"Svalbard And Jan Mayen"],
            ["country_code"=>"SK","country_name"=>"Slovakia"],
            ["country_code"=>"SL","country_name"=>"Sierra Leone"],
            ["country_code"=>"SM","country_name"=>"San Marino"],
            ["country_code"=>"SN","country_name"=>"Senegal"],
            ["country_code"=>"SR","country_name"=>"Suriname"],
            ["country_code"=>"ST","country_name"=>"Sao Tome And Principe"],
            ["country_code"=>"SV","country_name"=>"El Salvador"],
            ["country_code"=>"SZ","country_name"=>"Swaziland"],
            ["country_code"=>"TC","country_name"=>"Turks And Caicos Islands"],
            ["country_code"=>"TD","country_name"=>"Chad"],
            ["country_code"=>"TF","country_name"=>"French Southern Territories"],
            ["country_code"=>"TG","country_name"=>"Togo"],
            ["country_code"=>"TH","country_name"=>"Thailand"],
            ["country_code"=>"TJ","country_name"=>"Tajikistan"],
            ["country_code"=>"TK","country_name"=>"Tokelau"],
            ["country_code"=>"TL","country_name"=>"Timor-Leste"],
            ["country_code"=>"TM","country_name"=>"Turkmenistan"],
            ["country_code"=>"TN","country_name"=>"Tunisia"],
            ["country_code"=>"TO","country_name"=>"Tonga"],
            ["country_code"=>"TR","country_name"=>"Turkey"],
            ["country_code"=>"TT","country_name"=>"Trinidad and Tobago"],
            ["country_code"=>"TV","country_name"=>"Tuvalu"],
            ["country_code"=>"TW","country_name"=>"Taiwan"],
            ["country_code"=>"TZ","country_name"=>"Tanzania"],
            ["country_code"=>"UA","country_name"=>"Ukraine"],
            ["country_code"=>"UG","country_name"=>"Uganda"],
            ["country_code"=>"UM","country_name"=>"United States Minor Outlying Islands"],
            ["country_code"=>"US","country_name"=>"United States"],
            ["country_code"=>"UY","country_name"=>"Uruguay"],
            ["country_code"=>"UZ","country_name"=>"Uzbekistan"],
            ["country_code"=>"VA","country_name"=>"Vatican"],
            ["country_code"=>"VC","country_name"=>"Saint Vincent And The Grenadines"],
            ["country_code"=>"VG","country_name"=>"British Virgin Islands"],
            ["country_code"=>"VI","country_name"=>"U.S. Virgin Islands"],
            ["country_code"=>"VN","country_name"=>"Vietnam"],
            ["country_code"=>"VU","country_name"=>"Vanuatu"],
            ["country_code"=>"WF","country_name"=>"Wallis And Futuna"],
            ["country_code"=>"WS","country_name"=>"Samoa"],
            ["country_code"=>"YT","country_name"=>"Mayotte"],
            ["country_code"=>"ZA","country_name"=>"South Africa"],
            ["country_code"=>"ZM","country_name"=>"Zambia"]
        ];
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('dr_country_of_origin'),
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
