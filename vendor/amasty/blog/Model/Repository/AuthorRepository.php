<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Repository;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Model\Author;
use Amasty\Blog\Model\AuthorFactory;
use Amasty\Blog\Model\ResourceModel\Author as AuthorResource;
use Amasty\Blog\Model\ResourceModel\Author\Collection;
use Amasty\Blog\Model\ResourceModel\Author\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class AuthorRepository implements AuthorRepositoryInterface
{
    /**
     * @var AuthorFactory
     */
    private $authorFactory;

    /**
     * @var AuthorResource
     */
    private $authorResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $authors;

    /**
     * @var CollectionFactory
     */
    private $authorCollectionFactory;

    public function __construct(
        AuthorFactory $authorFactory,
        AuthorResource $authorResource,
        CollectionFactory $authorCollectionFactory
    ) {
        $this->authorFactory = $authorFactory;
        $this->authorResource = $authorResource;
        $this->authorCollectionFactory = $authorCollectionFactory;
    }

    /**
     * @throws CouldNotSaveException
     */
    public function save(AuthorInterface $author): AuthorInterface
    {
        try {
            if ($author->getAuthorId()) {
                $author = $this->getById($author->getAuthorId())->addData($author->getData());
            } else {
                $author->setAuthorId(null);
            }
            $this->authorResource->save($author);
            unset($this->authors[$author->getAuthorId()]);
        } catch (\Exception $e) {
            if ($author->getAuthorId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save author with ID %1. Error: %2',
                        [$author->getAuthorId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new author. Error: %1', $e->getMessage()));
        }

        return $author;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $authorId): AuthorInterface
    {
        if (!isset($this->authors[$authorId])) {
            /** @var Author $author */
            $author = $this->authorFactory->create();
            $this->authorResource->load($author, $authorId);
            if (!$author->getAuthorId()) {
                throw new NoSuchEntityException(__('Author with specified ID "%1" not found.', $authorId));
            }
            $this->authors[$authorId] = $author;
        }

        return $this->authors[$authorId];
    }

    public function getByName(string $name): AuthorInterface
    {
        $author = $this->authorFactory->create();
        $this->authorResource->load($author, $name, AuthorInterface::NAME);

        return $author;
    }

    public function getByUrlKey(?string $urlKey): AuthorInterface
    {
        return $this->getByUrlKeyAndStoreId($urlKey);
    }

    public function getByUrlKeyAndStoreId(?string $urlKey, int $storeId = Store::DEFAULT_STORE_ID): AuthorInterface
    {
        $collection = $this->authorCollectionFactory->create();
        $collection->addStoreWithDefault((int)$storeId);
        $collection->addStoreFieldToFilter(AuthorInterface::URL_KEY, $urlKey);
        $collection->setLimit(1);
        /** @var AuthorInterface $authorByUrlKey **/
        $authorByUrlKey = $collection->getFirstItem();

        return $authorByUrlKey;
    }

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(AuthorInterface $author): bool
    {
        try {
            $this->authorResource->delete($author);
            unset($this->authors[$author->getAuthorId()]);
        } catch (\Exception $e) {
            if ($author->getAuthorId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove author with ID %1. Error: %2',
                        [$author->getAuthorId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove author. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $authorId): bool
    {
        $authorModel = $this->getById($authorId);
        $this->delete($authorModel);

        return true;
    }

    public function getList(array $authors): Collection
    {
        return $this->authorCollectionFactory->create()->addFieldToFilter(AuthorInterface::NAME, ['in' => $authors]);
    }

    public function getAuthorModel(): AuthorInterface
    {
        return $this->authorFactory->create();
    }

    public function getAuthorCollection(): Collection
    {
        return $this->authorCollectionFactory->create()->addDefaultStore();
    }

    public function createAuthor(string $name, string $facebookProfile, string $twitterProfile): AuthorInterface
    {
        return $this->authorResource->createAuthor($name, $facebookProfile, $twitterProfile);
    }

    public function getByIdAndStore(?int $authorId, ?int $storeId = 0, bool $isAddDefaultStore = true): AuthorInterface
    {
        $collection = $this->authorCollectionFactory->create();
        if ($isAddDefaultStore) {
            $collection->addStoreWithDefault((int)$storeId);
        } else {
            $collection->addStore($storeId);
        }

        $collection->addFieldToFilter(AuthorInterface::AUTHOR_ID, $authorId);
        $collection->setLimit(1);

        return $collection->getFirstItem();
    }
}
