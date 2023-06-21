<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\RelatedProducts\Products;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface as EventManager;

class AddReviewsDataModifier implements CollectionModifierInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        State $state,
        EventManager $eventManager
    ) {
        $this->state = $state;
        $this->eventManager = $eventManager;
    }

    public function modify(ProductCollection $collection): void
    {
        $this->state->emulateAreaCode(Area::AREA_FRONTEND, [$this, 'addReviewsData'], [$collection]);
    }

    public function addReviewsData(Collection $collection): void
    {
        $this->eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );
    }
}
