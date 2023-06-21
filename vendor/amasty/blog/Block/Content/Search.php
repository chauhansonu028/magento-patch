<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\CategoryRepositoryInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Block\Content\Search\Section;
use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\Blog\Registry;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\ListsFactory;
use Amasty\Blog\Model\ResourceModel\Author\CollectionFactory as AuthorCollectionFactory;
use Amasty\Blog\Model\ResourceModel\Categories\CollectionFactory as CategoryCollectionFactory;
use Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory as PostCollectionFactory;
use Amasty\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Amasty\Blog\Model\Source\PostStatus;
use Amasty\Blog\Model\UrlResolver;
use Amasty\Blog\ViewModel\Author\SmallImage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template\Context;

class Search extends Lists
{
    public const SPECIAL_CHARACTERS = '+~/<>\':*$#@()!,.?`=%&^“';

    /**
     * @var string[]
     */
    private $collectionMapping = [
        'categories' => 'addCategoryFilter',
        'tags' => 'addTagFilter',
    ];

    /**
     * @var array
     */
    private $collectionFactories;

    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Settings $settingsHelper,
        Url $urlHelper,
        TagRepositoryInterface $tagRepository,
        AuthorRepositoryInterface $authorRepository,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        ListsFactory $listsModel,
        Date $helperDate,
        UrlResolver $urlResolver,
        Registry $registry,
        ConfigProvider $configProvider,
        SmallImage $smallImage,
        PostCollectionFactory $postCollectionFactory,
        array $collectionFactories,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $dataHelper,
            $settingsHelper,
            $urlHelper,
            $tagRepository,
            $authorRepository,
            $categoryRepository,
            $postRepository,
            $listsModel,
            $helperDate,
            $urlResolver,
            $registry,
            $configProvider,
            $smallImage,
            $data
        );

        $this->collectionFactories = $collectionFactories;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * @throws LocalizedException
     */
    protected function prepareBreadcrumbs(): void
    {
        parent::prepareBreadcrumbs();

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $title = __("Search results for '%1'", $this->getQueryText());
            $breadcrumbs->addCrumb(
                'search',
                [
                    'label' => $title,
                    'title' => $title,
                ]
            );
        }
    }

    public function getSearchSectionBlock(AbstractCollection $collection, string $name): Section
    {
        return $this
            ->getLayout()
            ->createBlock(Section::class)
            ->setTemplate(sprintf('Amasty_Blog::search/list/%s.phtml', $name))
            ->setData([
                'collection' => $collection,
                'entityName' => $name,
                'parentBlock' => $this
            ]);
    }

    public function getCollections(): array
    {
        $collections = [];
        /** @var PostCollectionFactory|CategoryCollectionFactory|TagCollectionFactory|AuthorCollectionFactory $collectionFactory */
        foreach ($this->collectionFactories as $key => $collectionFactory) {
            $collection = $collectionFactory->create();
            if (!$this->getQueryText()) {
                $collection->getSelect()->where('0 = 1');
            } else {
                $collection->setQueryText($this->getQueryText());
                $storeId = (int)$this->_storeManager->getStore()->getId();
                if (isset($this->collectionMapping[$key])) {
                    $collection->addStoreWithDefault($storeId);
                    $collection->load();
                    $postCollection = $this->postCollectionFactory->create();
                    $entityFilterMethod = $this->collectionMapping[$key];
                    if (method_exists($postCollection, $entityFilterMethod)) {
                        $postCollection->$entityFilterMethod($collection->getAllIds());
                    }

                    $collection = $postCollection;
                }

                if (method_exists($collection, 'addFilterByStatus')) {
                    $collection->addFilterByStatus([PostStatus::STATUS_ENABLED]);
                }
                $collection->addStoreWithDefault($storeId);
            }

            $collections[$key] = $collection;
        }

        return $collections;
    }

    /**
     * @return string
     */
    private function getQueryText()
    {
        $replaceSymbols = str_split(self::SPECIAL_CHARACTERS);
        $query = $this->getRequest()->getParam('query', '');

        return str_replace($replaceSymbols, '', trim($query));
    }
}
