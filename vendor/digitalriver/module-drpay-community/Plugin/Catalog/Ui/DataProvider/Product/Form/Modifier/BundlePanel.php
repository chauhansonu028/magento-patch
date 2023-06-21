<?php
/**
 * Create DR Allocated Percent field
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Digitalriver\DrPay\Helper\Config;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel as CoreBundlePanel;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

class BundlePanel
{
    /**
     * @var Config
     */
    private $drConfig;

    /**
     * @param Config $drConfig
     */
    public function __construct(
        Config $drConfig
    ) {
        $this->drConfig = $drConfig;
    }
    
    /**
     * @param CoreBundlePanel $subject
     * @param $meta
     * @return array
     */
    public function afterModifyMeta(CoreBundlePanel $subject, $meta)
    {
        if ($this->drConfig->getIsEnabled()) {
            $fieldSet = [
                'dr_allocated_percent' => [
                    'dataType' => Text::NAME,
                    'formElement' => Input::NAME,
                    'component' => 'Digitalriver_DrPay/js/form/element/input',
                    'label' => 'Digital River Allocated %',
                    'dataScope' => 'dr_allocated_percent',
                    'sortOrder' => 40,
                    'placeholder' => '0'
                ]
            ];
    
            foreach ($fieldSet as $filed => $fieldOptions) {
                $meta['bundle-items']['children']['bundle_options']['children']
                ['record']['children']['product_bundle_container']['children']['option_info']['children'][$filed] =
                    $this->getSelectionCustomText($fieldOptions);
            }
        }

        return $meta;
    }

    /**
     * @param $fieldOptions
     * @return \array[][][]
     */
    protected function getSelectionCustomText($fieldOptions)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'dataType' => $fieldOptions['dataType'],
                        'component' => $fieldOptions['component'],
                        'formElement' => $fieldOptions['formElement'],
                        'label' => __($fieldOptions['label']),
                        'dataScope' => $fieldOptions['dataScope'],
                        'sortOrder' => $fieldOptions['sortOrder'],
                        'placeholder' => $fieldOptions['placeholder'] ?? '',
                        'options' => $fieldOptions['options'] ?? ''
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $sortOrder
     * @param array $options
     * @return array
     */
    protected function getSkuFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('DR Allocated %'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => 'option_info',
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true,
                                'validate-alphanum-with-spaces' => true
                            ]
                        ]
                    ]
                ]
            ],
            $options
        );
    }
}
