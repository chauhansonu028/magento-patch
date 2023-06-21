<?php
/**
 * Provides DR Attributes options for template in product edit/new/bulk update page
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Catalog\Product;

use Digitalriver\DrPay\Model\ResourceModel\TaxTypes\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Digitalriver\DrPay\Model\ResourceModel\EccnCode\CollectionFactory as EccnCodeCollectionFactory;
use Digitalriver\DrPay\Model\ResourceModel\CountryOfOrigin\CollectionFactory as CountryOfOriginCollectionFactory;

/**
 * Get collection of DR custom attributes from custom table.
 * Class AttributeValues
 */
class AttributeValues extends Template
{
    /**
     * @var CollectionFactory
     */
    private $taxTypes;

    /**
     * @var EccnCodeCollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CountryOfOriginCollectionFactory
     */
    private $countryCollectionFactory;
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        CollectionFactory $taxTypes,
        EccnCodeCollectionFactory $collectionFactory,
        CountryOfOriginCollectionFactory $countryCollectionFactory,
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->taxTypes = $taxTypes;
        $this->collectionFactory = $collectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Retrieve json
     *
     * @return string
     */
    public function getTaxValues(): string
    {
        $dataCollection = $this->taxTypes->create()->getData();
        return $this->jsonSerializer->serialize($dataCollection);
    }

    /**
     * returns ECCN attribute details
     *
     * @return array
     */
    public function getEccnDetails(): array
    {
        $eccnCode = $this->collectionFactory->create();
        $eccnData = $eccnCode->getData();

        $options = [];
        foreach ($eccnData as $item) {
            $options []  =
                [
                    'classification_code' => $item['classification_code'],
                    'description' => $item['description'],
                    'notes' => $item['notes']
                ];
        }
        return $options;
    }

    /**
     * returns Country attribute details
     *
     * @return array
     */
    public function getCountryDetails(): array
    {
        $country = $this->countryCollectionFactory->create();
        $countryData = $country->getData();

        $options = [];
        foreach ($countryData as $item) {
            $options [$item['country_code']]  = $item['country_name'];
        }
        return $options;
    }
}
