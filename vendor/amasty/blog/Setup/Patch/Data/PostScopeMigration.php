<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Model\ResourceModel\Posts;
use Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory as PostCollectionFactory;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ResourceModel\Store\Collection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Magento\Store\Model\Store;

class PostScopeMigration implements DataPatchInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StoreCollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    public function __construct(
        State $appState,
        ModuleDataSetupInterface $moduleDataSetup,
        StoreCollectionFactory $storeCollectionFactory,
        PostCollectionFactory $postCollectionFactory,
        PostRepositoryInterface $postRepository
    ) {
        $this->appState = $appState;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->postRepository = $postRepository;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [$this, 'migrationPostToScope']
        );

        return $this;
    }

    public function migrationPostToScope(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $storeTableName = $this->moduleDataSetup->getTable(Posts::STORE_TABLE_NAME);
        /** @var Collection $storeCollection */
        $storeCollection = $this->storeCollectionFactory->create();
        $allStoreViews = $storeCollection->getAllIds();
        $collection = $this->postCollectionFactory->create();
        /** @var \Amasty\Blog\Model\Posts $post */
        foreach ($collection as $post) {
            $postEntity = $this->postRepository->getById($post->getPostId());
            $postStores = $postEntity->getStores();
            $disabledPostStores = array_diff($allStoreViews, $postStores);
            $defaultPostIsDisabled = in_array(Store::DEFAULT_STORE_ID, $disabledPostStores);
            $defaultPostData = [
                PostInterface::TITLE => $post->getTitle(),
                PostInterface::META_TITLE => $post->getMetaTitle(),
                PostInterface::META_DESCRIPTION => $post->getMetaDescription(),
                PostInterface::META_TAGS => $post->getMetaTags(),
                PostInterface::META_ROBOTS => $post->getMetaRobots(),
                PostInterface::CANONICAL_URL => $post->getCanonicalUrl(),
                PostInterface::POST_THUMBNAIL_ALT => $post->getPostThumbnailAlt(),
                PostInterface::LIST_THUMBNAIL_ALT => $post->getListThumbnailAlt(),
                PostInterface::SHORT_CONTENT => $post->getData(PostInterface::SHORT_CONTENT),
                PostInterface::FULL_CONTENT => $post->getData(PostInterface::FULL_CONTENT),
                PostInterface::STATUS => $defaultPostIsDisabled ? PostStatus::STATUS_DISABLED : $post->getStatus(),
                PostInterface::PUBLISHED_AT => $post->getPublishedAt(),
            ];
            if ($defaultPostIsDisabled) {
                $connection->insert(
                    $storeTableName,
                    [
                        PostInterface::POST_ID => $post->getPostId(),
                        PostInterface::STORE_ID => Store::DEFAULT_STORE_ID
                    ] + $defaultPostData
                );

                foreach ($postStores as $postStore) {
                    $connection->update($storeTableName, [
                        PostInterface::STATUS => $post->getStatus(),
                        PostInterface::PUBLISHED_AT => $post->getPublishedAt(),
                    ], [
                        PostInterface::POST_ID . ' = ?' => $post->getPostId(),
                        PostInterface::STORE_ID . ' = ?' => $postStore,
                    ]);
                }

                foreach ($disabledPostStores as $disabledPostStore) {
                    if ($disabledPostStore == Store::DEFAULT_STORE_ID) {
                        continue;
                    }

                    $connection->insert($storeTableName, [
                        PostInterface::POST_ID => $post->getPostId(),
                        PostInterface::STORE_ID => $disabledPostStore,
                        PostInterface::STATUS => PostStatus::STATUS_DISABLED,
                    ]);
                }
            } else {
                $connection->update($storeTableName, $defaultPostData, [
                    PostInterface::POST_ID . ' = ?' => $post->getPostId(),
                    PostInterface::STORE_ID . ' = ?' => Store::DEFAULT_STORE_ID,
                ]);
            }
        }
    }
}
