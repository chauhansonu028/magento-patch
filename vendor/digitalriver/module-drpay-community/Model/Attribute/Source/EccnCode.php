<?php

/**
 * Provides EccnCode attribute options.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Source;

use Digitalriver\DrPay\Model\ResourceModel\EccnCode\CollectionFactory as EccnCodeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\Collection;

/**
 * Class EccnCode Source Provider
 */
class EccnCode extends DrPayAbstractSource
{
    private $eccnCodeFactory;

    /**
     * EccnCode constructor.
     * @param EccnCodeCollectionFactory $eccnCodeFactory
     */
    public function __construct(
        EccnCodeCollectionFactory $eccnCodeFactory
    ) {
        $this->eccnCodeFactory = $eccnCodeFactory;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $eccnCode = $this->eccnCodeFactory->create();
        $eccnData = $eccnCode->getData();

        if (!$this->_options) {
            $this->_options []  =
                ['label' => '-- Select One --', 'value' => '-- Select One --'];
            foreach ($eccnData as $item) {
                $this->_options []  =
                    ['label' => __($item['classification_code']), 'value' => $item['classification_code']];
            }
        }
        return $this->_options;
    }
}
