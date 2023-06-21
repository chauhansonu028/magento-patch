<?php
/**
 * DR Tax Types Source
 *
 * Provides Tax Types attribute options.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Source;

use Digitalriver\DrPay\Model\ResourceModel\TaxTypes\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Registry;

/**
 * Custom options for DR tax type select element
 * Class TaxType
 */
class TaxType extends DrPayAbstractSource
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var CollectionFactory
     */
    private $taxTypes;

    /**
     * TaxType constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param CollectionFactory $taxTypes
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        CollectionFactory $taxTypes
    ) {
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->taxTypes = $taxTypes;
    }

    /**
     * Get all DR Tax Types options
     *
     * @return array|null
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options[] = ['label' => __('--Select--'), 'value' => ''];
            $product = $this->registry->registry('current_product');

            if (is_object($product)) {
                if ($product->getCustomAttribute('dr_tax_group') !== null) {
                    $taxGroupValue = $product->getCustomAttribute('dr_tax_group')->getValue();
                    $datacollection = $this->taxTypes->create()->getData();

                    foreach ($datacollection as $item) {
                        if ($item['dr_tax_group'] === $taxGroupValue) {
                            $this->_options[] = ['label' => __($item['dr_tax_type']), 'value' => $item['entity_id']];
                        }
                    }
                }
            } else {
                $datacollection = $this->taxTypes->create()->getData();
                foreach ($datacollection as $item) {
                    $this->_options[] = ['label' => __($item['dr_tax_type']), 'value' => $item['entity_id']];
                }
            }
        }
        return $this->_options;
    }
}
