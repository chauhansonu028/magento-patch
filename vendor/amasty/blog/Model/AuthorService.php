<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Model\Blog\Registry;
use Magento\Store\Model\StoreManagerInterface;

class AuthorService
{
    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AuthorInterface
     */
    private $currentAuthor;

    public function __construct(
        AuthorRepositoryInterface $authorRepository,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->authorRepository = $authorRepository;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
    }

    public function getCurrentAuthor(?int $authorId = null): ?AuthorInterface
    {
        if ($this->currentAuthor === null) {
            if ($authorId) {
                $this->currentAuthor = $this->getAuthor($authorId);
            } else {
                $currentPost = $this->registry->registry(Registry::CURRENT_POST);
                if ($currentPost && $currentPost->getAuthorId()) {
                    $this->currentAuthor = $this->getAuthor((int)$currentPost->getAuthorId());
                }
            }
        }

        return $this->currentAuthor;
    }

    private function getAuthor(int $authorId): ?AuthorInterface
    {
        try {
            /** @var AuthorInterface $author */
            $storeId = (int)$this->storeManager->getStore()->getId();
            $author = $this->authorRepository->getByIdAndStore($authorId, $storeId);
        } catch (\Exception $e) {
            $author = null;
        }

        return $author;
    }
}
