<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Setup\Patch\Data;

use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ResourceModel\Author as AuthorResource;
use Amasty\Blog\Model\ResourceModel\Posts as PostsResource;
use Amasty\Blog\Setup\Patch\DeclarativeSchemaApplyBefore\ExtractDataAboutAuthorFromPost;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;

class LinkPostsAndAuthors implements DataPatchInterface, NonTransactionableInterface
{
    /**
     * @var AuthorResource
     */
    private $authorResource;

    /**
     * @var AuthorInterface[]
     */
    private $authorsCache = [];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AuthorResource $authorResource
    ) {
        $this->authorResource = $authorResource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $connection = $setup->getConnection();
        $tmpTableName = $setup->getTable(ExtractDataAboutAuthorFromPost::TEMPORARY_TABLE_NAME);

        if ($connection->isTableExists($tmpTableName)) {
            $connection->beginTransaction();

            try {
                $select = $connection->select();
                $select->from($tmpTableName);
                $authorData = $connection->fetchAll($select) ?? [];
                $authorPostRelations = $this->prepareAuthorPostRelations($authorData);

                if (!empty($authorPostRelations)) {
                    $connection->insertOnDuplicate(
                        $setup->getTable(PostsResource::TABLE_NAME),
                        $authorPostRelations,
                        [AuthorInterface::AUTHOR_ID]
                    );
                }

                $connection->commit();
                $connection->dropTable($tmpTableName);
            } catch (\Throwable $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    private function getOrCreateAuthor(array $authorData): AuthorInterface
    {
        if (!isset($this->authorsCache[$authorData[PostInterface::POSTED_BY]])) {
            $this->authorsCache[$authorData[PostInterface::POSTED_BY]] = $this->authorResource->createAuthor(
                $authorData[PostInterface::POSTED_BY],
                $authorData[AuthorInterface::FACEBOOK_PROFILE],
                $authorData[AuthorInterface::TWITTER_PROFILE]
            );
        }

        return $this->authorsCache[$authorData[PostInterface::POSTED_BY]];
    }

    /**
     * @param array $authorData
     * @return array[]
     */
    private function prepareAuthorPostRelations(array $authorData): array
    {
        return array_map(function (array $authorDataByPost): array {
            return [
                PostInterface::POST_ID => $authorDataByPost[PostInterface::POST_ID],
                PostInterface::AUTHOR_ID => $this->getOrCreateAuthor($authorDataByPost)->getAuthorId()
            ];
        }, $authorData);
    }
}
