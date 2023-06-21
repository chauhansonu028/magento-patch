<?php
/**
 * DR Tax Groups Source
 *
 * Provides Tax Groups attribute options.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Source;

use Digitalriver\DrPay\Model\ResourceModel\TaxTypes\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\Collection;

/**
 * Custom options for DR tax group select element
 * Class TaxGroup
 */
class TaxGroup extends DrPayAbstractSource
{
    /**
     * @var CollectionFactory
     */
    private $taxGroups;

    /**
     * TaxGroup constructor.
     * @param CollectionFactory $taxGroups
     */
    public function __construct(
        CollectionFactory $taxGroups
    ) {
        $this->taxGroups = $taxGroups;
    }

    /**
     * Get all DR Tax Group options
     *
     * @return array|null
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {

            $dataCollection = $this->taxGroups->create()->getData();

            $temp = array_unique(array_column($dataCollection, 'dr_tax_group'));
            $unique_arr = array_intersect_key($dataCollection, $temp);

            $this->_options[] = ['label' => __('--Select--'), 'value' => ''];

            foreach ($unique_arr as $item) {
                $this->_options[] = ['label' => __($item['dr_tax_group']), 'value' => $item['dr_tax_group']];
            }
        }
        return $this->_options;
    }
}
