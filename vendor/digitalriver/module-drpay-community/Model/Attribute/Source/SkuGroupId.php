<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Attribute\Source;

use Digitalriver\DrPay\Api\SkuGroupProviderInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class SkuGroupId
 *
 * Returns a list of available group ids for SKU Group ID attribute
 */
class SkuGroupId extends AbstractSource
{
    /**
     * @var SkuGroupProviderInterface
     */
    private $skuGroupProvider;

    /**
     * SkuGroupId constructor.
     * @param SkuGroupProviderInterface $skuGroupProvider
     */
    public function __construct(SkuGroupProviderInterface $skuGroupProvider)
    {
        $this->skuGroupProvider = $skuGroupProvider;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $groups = $this->skuGroupProvider->getSkuGroups();
        $options = [
            ['label' => __('None'), 'value' => 0]
        ];

        foreach ($groups as $group) {
            if (isset($group['alias'])) {
                $label = sprintf("%s (%s)", $group['alias'], $group['id']);
            } else {
                $label = $group['id'];
            }
            $options[] = ['label' => $label, 'value' => $group['id']];
        }

        return $options;
    }
}
