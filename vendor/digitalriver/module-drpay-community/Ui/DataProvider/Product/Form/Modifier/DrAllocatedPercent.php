<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Customize dr allocated percent field
 */
class DrAllocatedPercent extends AbstractModifier
{

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(
        LocatorInterface $locator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->locator = $locator;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        $isBundleProduct = $product->getTypeId() === Type::TYPE_CODE;

        if ($isBundleProduct && $modelId) {
            $extraOptions = $this->getAdditionalAttributes($product);

            foreach ($data[$modelId][BundlePanel::CODE_BUNDLE_OPTIONS][BundlePanel::CODE_BUNDLE_OPTIONS] as &$option) {
                $option['dr_allocated_percent'] = $extraOptions[$option['option_id']]['dr_allocated_percent'];
            }
        }
        return $data;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAdditionalAttributes($product)
    {
        $optionArray = [];
        $product = $this->productRepository->getById($product->getId());
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionArray[$options->getOptionId()]['dr_allocated_percent'] = $options->getData('dr_allocated_percent');
        }

        return $optionArray;
    }
}
