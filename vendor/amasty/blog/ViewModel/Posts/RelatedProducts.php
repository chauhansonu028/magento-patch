<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Posts;

use Amasty\Blog\Api\Data\GetPostRelatedProductsInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render as PriceRender;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;

class RelatedProducts implements ArgumentInterface
{
    private const IMAGE_ID = 'amasty_blog_related_products_thumbnail';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetPostRelatedProductsInterface
     */
    private $getPostRelatedProducts;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var ReviewRendererInterface
     */
    private $reviewRenderer;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var PriceRender
     */
    private $priceRenderer;

    /**
     * @var ProductsList
     */
    private $productList;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        Registry $registry,
        GetPostRelatedProductsInterface $getPostRelatedProducts,
        ConfigProvider $configProvider,
        ImageFactory $imageFactory,
        ReviewRendererInterface $reviewRenderer,
        LayoutInterface $layout,
        ProductsList $productList,
        SerializerInterface $serializer
    ) {
        $this->registry = $registry;
        $this->getPostRelatedProducts = $getPostRelatedProducts;
        $this->configProvider = $configProvider;
        $this->imageFactory = $imageFactory;
        $this->reviewRenderer = $reviewRenderer;
        $this->layout = $layout;
        $this->productList = $productList;
        $this->serializer = $serializer;
    }

    /**
     * @return PostInterface
     */
    public function getCurrentPost(): PostInterface
    {
        return $this->registry->registry(Registry::CURRENT_POST);
    }

    /**
     * @return array
     */
    public function getPostProducts(): array
    {
        $post = $this->getCurrentPost();

        return $this->getPostRelatedProducts->execute($post->getPostId());
    }

    /**
     * @return string
     */
    public function getRelatedProductsBlockName(): string
    {
        return $this->configProvider->getPostPageBlockTitleOnPostPage();
    }

    /**
     * @return bool
     */
    public function isCanRender(): bool
    {
        return count($this->getPostProducts()) > 0;
    }

    /**
     * @param Product $product
     * @return string|null
     */
    public function getImageHtml(Product $product): ?string
    {
        return $this->imageFactory->create($product, self::IMAGE_ID, [])->toHtml();
    }

    /**
     * @param Product $product
     * @return string|null
     */
    public function getReviewsHtml(Product $product): ?string
    {
        return $this->reviewRenderer->getReviewsSummaryHtml($product, ReviewRendererInterface::SHORT_VIEW);
    }

    /**
     * @return PriceRender
     */
    private function getPriceRenderer(): PriceRender
    {
        if (!$this->priceRenderer) {
            $this->priceRenderer = $this->layout->createBlock(
                PriceRender::class,
                '',
                ['data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]]
            );
        }

        return $this->priceRenderer;
    }

    /**
     * @param Product $product
     * @return string|null
     */
    public function getProductPriceHtml(Product $product): ?string
    {
        return $this->getPriceRenderer()->render(FinalPrice::PRICE_CODE, $product);
    }

    /**
     * @param $product
     * @return string
     */
    public function getAddToCartPostParams($product): string
    {
        return $this->serializer->serialize($this->productList->getAddToCartPostParams($product));
    }
}
