<?php
/**
 * Provides countries information.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Model\ResourceModel\CountryOfOrigin as CountryOfOriginResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class CountryOfOrigin Model
 */
class CountryOfOrigin extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'dr_country_of_origin';

    /**
     * @var string
     */
    protected $_cacheTag = 'dr_country_of_origin';

    /**
     * @var string
     */
    protected $_eventPrefix = 'dr_country_of_origin';

    /**
     * @var string
     */
    protected $countryCode = 'dr_country_code';

    /**
     * @var string
     */
    protected $countryName = 'dr_country_name';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(CountryOfOriginResource::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues(): array
    {
        return [];
    }

    /**
     * Returns country code
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Sets the country code
     *
     * @param string $countryCode
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Returns country name
     *
     * @return string
     */
    public function getCountryName(): string
    {
        return $this->countryName;
    }

    /**
     * Sets the country name
     *
     * @param string $countryName
     */
    public function setCountryName(string $countryName): void
    {
        $this->countryName = $countryName;
    }
}
