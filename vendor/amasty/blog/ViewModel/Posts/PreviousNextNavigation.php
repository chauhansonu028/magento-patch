<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\ViewModel\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\ResourceModel\Posts\NavigationPosition;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class PreviousNextNavigation implements ArgumentInterface
{
    public const NEXT = 'next';
    public const PREVIOUS = 'previous';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var NavigationPosition
     */
    private $navigationPosition;

    /**
     * @var array
     */
    private $positions = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    public function __construct(
        ConfigProvider $configProvider,
        Registry $registry,
        NavigationPosition $navigationPosition,
        StoreManagerInterface $storeManager,
        PostRepositoryInterface $postRepository
    ) {
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->navigationPosition = $navigationPosition;
        $this->storeManager = $storeManager;
        $this->postRepository = $postRepository;
    }

    /**
     * @return array|null
     */
    public function getPreviousData(): ?array
    {
        return  $this->getData(self::PREVIOUS);
    }

    /**
     * @return array|null
     */
    public function getNextData(): ?array
    {
        return  $this->getData(self::NEXT);
    }

    private function getData(string $direction): ?array
    {
        $post = $this->registry->registry(Registry::CURRENT_POST);
        if ($post && $this->configProvider->isPreviousNextNavigation()) {
            $currentPosition = $this->getPositionById($post->getPostId());
            $position = $direction === self::NEXT ? ++$currentPosition : --$currentPosition;
            if (isset($this->getPositions()[$position])) {
                $post = $this->postRepository->getById((int)$this->getPositions()[$position]);
                $data = $post->getData();
                $data[PostInterface::URL_KEY] = $post->getUrl();
            }
        }

        return $data ?? null;
    }

    private function getPositionById(int $id)
    {
        return array_search($id, $this->getPositions());
    }

    private function getPositions(): array
    {
        if (!$this->positions) {
            $this->positions = $this->navigationPosition->getPositions($this->getStores());
        }

        return $this->positions;
    }

    private function getStores(): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        return [PostInterface::ALL_STORE_VIEWS, $storeId];
    }
}
