<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Api;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Model\ResourceModel\Author\Collection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * @api
 */
interface AuthorRepositoryInterface
{
    public function save(AuthorInterface $author): AuthorInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $authorId): AuthorInterface;

    /**
     * @deprecared since version 2.7.0 Now url key can be configured by store view
     * @see \Amasty\Blog\Api\AuthorRepositoryInterface::getByUrlKeyAndStoreId
     */
    public function getByUrlKey(?string $urlKey): AuthorInterface;

    public function getByUrlKeyAndStoreId(?string $urlKey, int $storeId = Store::DEFAULT_STORE_ID): AuthorInterface;

    public function getByName(string $name): AuthorInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(AuthorInterface $author): bool;

    /**
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $authorId): bool;

    public function getList(array $authors): Collection;

    public function getAuthorModel(): AuthorInterface;

    public function getAuthorCollection(): Collection;

    public function createAuthor(string $name, string $facebookProfile, string $twitterProfile): AuthorInterface;

    public function getByIdAndStore(int $authorId, ?int $storeId = 0, bool $isAddDefaultStore = true): AuthorInterface;
}
