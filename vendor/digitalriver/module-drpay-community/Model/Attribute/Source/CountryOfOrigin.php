<?php
/**
 * Provides countries options.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Source;

use Digitalriver\DrPay\Model\ResourceModel\CountryOfOrigin\CollectionFactory as CountryOfOriginCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\Collection;

/**
 * Class CountryOfOrigin Source Provider
 */
class CountryOfOrigin extends DrPayAbstractSource
{
    /**
     * @var CountryOfOriginCollectionFactory
     */
    private $countryOfOriginFactory;

    /**
     * CountryOfOrigin constructor.
     * @param CountryOfOriginCollectionFactory $countryOfOriginFactory
     */
    public function __construct(
        CountryOfOriginCollectionFactory $countryOfOriginFactory
    ) {
        $this->countryOfOriginFactory = $countryOfOriginFactory;
    }

    /**
     * Get all countries options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $countryOfOrigin = $this->countryOfOriginFactory->create();
        $countryOfOrigin->setOrder('country_name', 'ASC');

        $countryData = $countryOfOrigin->getData();

        if (!$this->_options) {
            $this->_options []  =
                ['label' => '-- Select One --', 'value' => false];
            foreach ($countryData as $item) {
                $this->_options []  =
                    ['label' => __($item['country_code']), 'value' => $item['country_code']];
            }
        }
        return $this->_options;
    }
}
