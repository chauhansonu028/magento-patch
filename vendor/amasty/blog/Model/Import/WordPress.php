<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Import;

use Amasty\Blog\Controller\Adminhtml\Import\Import as ImportRegistry;
use Amasty\Blog\Model\Source\CommentStatus;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\UrlInterface;

class WordPress extends AbstractImport
{
    public const SPAM_COMMENT = 'spam';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var array
     */
    private $importedTags = [];

    /**
     * @var array
     */
    private $importedCategories = [];

    /**
     * @var array
     */
    private $importedAuthors = [];

    /**
     * @var array
     */
    private $importedPosts = [];

    /**
     * @var array
     */
    private $importedComments = [];

    /**
     * @var \mysqli
     */
    private $connection;

    /**
     * @var bool
     */
    private $isMetaTaxonomyLoaded = false;

    /**
     * @var null|array
     */
    private $metaTaxonomy = null;

    public function processImport()
    {
        $dbHost = $this->scopeConfig->getValue(ImportRegistry::AMBLOG_CRON_DBHOST);
        $dbUsername = $this->scopeConfig->getValue(ImportRegistry::AMBLOG_CRON_DBUSERNAME);
        $dbPassword = $this->scopeConfig->getValue(ImportRegistry::AMBLOG_CRON_DBPASSWORD);
        $dbName = $this->scopeConfig->getValue(ImportRegistry::AMBLOG_CRON_DBNAME);
        $data = [
            'prefix' => $this->scopeConfig->getValue(ImportRegistry::AMBLOG_CRON_PREFIX),
            'update' => $this->scopeConfig->getValue(ImportRegistry::AMBLOG_CRON_UPDATE),
        ];

        try {
            // @codingStandardsIgnoreStart
            $connection = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);
            $this->connection = $connection;
            $this->run($data, $connection);
            mysqli_close($connection);
            // @codingStandardsIgnoreEnd
        } catch (\Exception $exception) {
            $this->logger->error(__('Amasty Import error: ') . $exception->getMessage());
        }
    }

    /**
     * @param $data
     * @param $connection
     */
    public function run($data, $connection)
    {
        $this->prefix = $data['prefix'];
        $connection->query('SET NAMES "utf8"');
        $this->importTags($data, $connection);
        $this->importCategories($data, $connection);
        $this->importAuthors($data, $connection);
        $this->importPosts($data, $connection);
        $this->importComments($connection);
    }

    /**
     * @param $data
     * @param $connection
     * @return void|null
     */
    protected function importPosts($data, $connection)
    {
        try {
            // @codingStandardsIgnoreStart
            $sqlString =
                "SELECT * FROM " . $this->prefix . "posts WHERE post_type = 'post' AND post_status <> 'auto-draft'"
                . " AND post_name != ''";
            // @codingStandardsIgnoreEnd
            $result = $connection->query($sqlString);

            if (!$result) {
                return null;
            }

            while ($post = $result->fetch_assoc()) {
                if ($data['update']) {
                    $this->updatePost($post, $connection);
                } else {
                    $this->savePost($post, $connection);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Amasty Import error with Posts: ') . $exception->getMessage());
        }
    }

    /**
     * @param $post
     * @param $connection
     */
    protected function updatePost($post, $connection)
    {
        $postModel = $this->preparePostData($post, $connection);
        $existPost = $this->postRepository->getByUrlKeyWithAllStatuses($postModel->getUrlKey());
        $postModel->setPostId($existPost->getPostId());
        $this->postRepository->save($postModel);
        $this->importedPosts[$post['ID']] = $postModel->getPostId();
    }

    /**
     * @param $post
     * @param $connection
     */
    protected function savePost($post, $connection)
    {
        $postModel = $this->preparePostData($post, $connection);
        $existPost = $this->postRepository->getByUrlKey($postModel->getUrlKey());

        if ($existPost->getPostId()) {
            $postModel->setUrlKey($postModel->getUrlKey() . '-duplicate');
        }

        $this->postRepository->save($postModel);
        $this->importedPosts[$post['ID']] = $postModel->getPostId();
    }

    /**
     * @param $post
     * @param $connection
     * @return \Amasty\Blog\Api\Data\PostInterface
     */
    protected function preparePostData($post, $connection)
    {
        $postModel = $this->postRepository->getPost();
        $date = $this->dateTime->date();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        [$image, $alt] = $this->getPostBanner($post, $connection);
        $post['post_content'] = $this->preparePostContent($post['post_content']);
        $postData = [
            'status' => $this->getStatus($post),
            'title' => $post['post_title'],
            'url_key' => $post['post_name'],
            'short_content' => $post['post_excerpt']
                ?: mb_strimwidth(strip_tags($post['post_content']), 0, 400, '...'),
            'full_content' => $post['post_content'],
            'created_at' => $post['post_date'] > $date || !$post['post_date'] ? $date : $post['post_date'],
            'updated_at' => $post['post_modified'] ?: $date,
            'edited_at' => $post['post_modified'] ?: $date,
            'published_at' => $post['post_date'] ?: $date,
            'recently_commented_at' => '0000-00-00 00:00:00',
            'display_short_content' => 1,
            'comments_enabled' => 1,
            'post_thumbnail' => $image,
            'list_thumbnail' => $image,
            'post_thumbnail_alt' => $alt,
            'list_thumbnail_alt' => $alt,
            'stores' => [0],
            'tags' => $this->getRelationship($post, $connection, 'post_tag'),
            'categories' => $this->getRelationship($post, $connection, 'category'),
            'author_id' => $this->importedAuthors[$post['post_author']]
        ];

        $postData = array_merge(
            $postData,
            $this->getPostMeta($post)
        );

        $postModel->setData($postData);

        return $postModel;
    }

    public function preparePostContent(string $postContent): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $postContent = preg_replace(
            "/(https:\/\/)(.+?)(\/wp-content\/)/",
            $mediaUrl . "amasty/blog/",
            $postContent
        );
        $postContent = preg_replace(
            "/(http:\/\/)(.+?)(\/wp-content\/)/",
            $mediaUrl . "amasty/blog/",
            $postContent
        );

        return $postContent;
    }

    private function getPostMeta(array $post): array
    {
        $postMeta = [
            'meta_title' => $post['post_title'],
            'meta_description' => '',
            'meta_tags' => '',
            'meta_robots' => 'index, follow',
        ];
        $fieldPap = [
            '_yoast_wpseo_title' => 'meta_title',
            '_yoast_wpseo_metadesc' => 'meta_description',
        ];

        // @codingStandardsIgnoreStart
        $sqlString = "SELECT meta_key,meta_value FROM " . $this->prefix . "postmeta "
            . "WHERE `meta_key` like '%_yoast_wpseo%' AND post_id = " . $post['ID'];

        $result = $this->connection->query($sqlString);

        if (!$result) {
            return $postMeta;
        }

        $noIndex = null;
        $noFollow = null;
        while ($wpMeta = $result->fetch_assoc()) {
            if (isset($fieldPap[$wpMeta['meta_key']])) {
                $postMeta[$fieldPap[$wpMeta['meta_key']]] = $wpMeta['meta_value'];
            }

            if ($wpMeta['meta_key'] === '_yoast_wpseo_meta-robots-noindex') {
                $noIndex = true;
            }

            if ($wpMeta['meta_key'] === '_yoast_wpseo_meta-robots-nofollow') {
                $noFollow = true;
            }
        }

        if ($noIndex && $noFollow) {
            $postMeta['meta_robots'] = 'noindex, nofollow';
        } elseif ($noIndex && !$noFollow) {
            $postMeta['meta_robots'] = 'noindex, follow';
        } elseif ($noFollow && !$noIndex) {
            $postMeta['meta_robots'] = 'index, nofollow';
        }

        return $postMeta;
    }

    /**
     * @param $post
     * @return int
     */
    protected function getStatus($post)
    {
        switch ($post['post_status']) {
            case 'publish':
                $resultStatus = $post['post_password'] ? PostStatus::STATUS_HIDDEN : PostStatus::STATUS_ENABLED;
                break;
            case 'future':
                $resultStatus = PostStatus::STATUS_SCHEDULED;
                break;
            default:
                $resultStatus = PostStatus::STATUS_DISABLED;
                break;
        }

        return $resultStatus;
    }

    /**
     * @param $wordpressPost
     * @param $connection
     * @return array
     */
    protected function getPostBanner($wordpressPost, $connection)
    {
        // @codingStandardsIgnoreStart
        $sqlString = "SELECT * FROM " . $this->prefix . "postmeta AS meta "
            . "LEFT JOIN " . $this->prefix . "posts AS posts ON meta.post_id=posts.ID "
            . "WHERE `meta_key` = '_wp_attachment_metadata' AND `post_type` = 'attachment' "
            . "AND `post_parent` = '" . $wordpressPost['ID'] . "'";
        // @codingStandardsIgnoreEnd
        $result = $connection->query($sqlString);
        $banner = '';
        $alt = '';

        if ($result) {
            while ($post = $result->fetch_assoc()) {
                $metaValue = $this->serializer->unserialize($post['meta_value']);
                if (count($metaValue['sizes'] ?? [])) {
                    $banner = 'uploads/' . $metaValue['file'];
                    $alt = $this->getImageAlt($connection, $post['post_id']);
                    break;
                }
            }
        }

        return [$banner, $alt];
    }

    /**
     * @param $connection
     * @param $postId
     * @return mixed|string
     */
    protected function getImageAlt($connection, $postId)
    {
        // @codingStandardsIgnoreStart
        $sqlString = "SELECT meta_value FROM " . $this->prefix . "postmeta "
            . "WHERE `meta_key` = '_wp_attachment_image_alt' AND post_id = " . $postId;
        // @codingStandardsIgnoreEnd
        $result = $connection->query($sqlString);
        $altArray = [];

        if ($result) {
            $altArray = $result->fetch_assoc();
        }

        return isset($altArray['meta_value']) ? $altArray['meta_value'] : '';
    }

    /**
     * @param $wordpressPost
     * @param $connection
     * @param $type
     * @return string
     */
    protected function getRelationship($wordpressPost, $connection, $type)
    {
        $importedValues = $type == 'category' ? $this->importedCategories : $this->importedTags;
        $sqlString = $this->getRelationshipSql($wordpressPost['ID']) . ' AND taxonomy = "' . $type . '"';
        $result = $connection->query($sqlString);
        $values = '';

        if ($result) {
            while ($post = $result->fetch_assoc()) {
                if (isset($importedValues[$post['term_id']])) {
                    $values .= $importedValues[$post['term_id']] . ',';
                }
            }
        }

        return $values;
    }

    /**
     * @param $wordpressPostId
     * @return string
     */
    private function getRelationshipSql($wordpressPostId)
    {
        // @codingStandardsIgnoreStart
        return "SELECT * FROM " . $this->prefix . "posts
            LEFT JOIN " . $this->prefix . "term_relationships
            ON " . $this->prefix . "posts.ID=" . $this->prefix . "term_relationships.object_id
            LEFT JOIN " . $this->prefix . "term_taxonomy
            ON " . $this->prefix . "term_relationships.term_taxonomy_id=" . $this->prefix . "term_taxonomy.term_taxonomy_id
            WHERE `ID` = '" . $wordpressPostId . "'";
        // @codingStandardsIgnoreEnd
    }

    /**
     * @param $data
     * @param $connection
     * @return void|null
     */
    protected function importTags($data, $connection)
    {
        try {
            // @codingStandardsIgnoreStart
            $sqlString = "SELECT * FROM " . $this->prefix . "terms
                          INNER JOIN " . $this->prefix . "term_taxonomy
                          ON " . $this->prefix . "terms.term_id=" . $this->prefix . "term_taxonomy.term_id
                          WHERE " . $this->prefix . "term_taxonomy.taxonomy = 'post_tag'";
            // @codingStandardsIgnoreEnd
            $result = $connection->query($sqlString);

            if (!$result) {
                return null;
            }

            while ($tag = $result->fetch_assoc()) {
                if ($data['update']) {
                    $this->updateTag($tag);
                } else {
                    $this->saveTag($tag);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Amasty Import error with Tags: ') . $exception->getMessage());
        }
    }

    /**
     * @param $tag
     */
    protected function updateTag($tag)
    {
        $tagModel = $this->prepareTagData($tag);
        $existTag = $this->tagRepository->getByUrlKey($tagModel->getUrlKey());
        $tagModel->setTagId($existTag->getTagId());
        $this->tagRepository->save($tagModel);
        $this->importedTags[$tag['term_id']] = $tagModel->getName();
    }

    /**
     * @param $tag
     */
    protected function saveTag($tag)
    {
        $tagModel = $this->prepareTagData($tag);
        $existTag = $this->tagRepository->getByUrlKey($tagModel->getUrlKey());

        if ($existTag->getTagId()) {
            $tagModel->setUrlKey($tagModel->getUrlKey() . '-duplicate');
        }

        $this->tagRepository->save($tagModel);
        $this->importedTags[$tag['term_id']] = $tagModel->getName();
    }

    /**
     * @param $tag
     * @return \Amasty\Blog\Model\Tag
     */
    protected function prepareTagData($tag)
    {
        $tagModel = $this->tagRepository->getTagModel();
        $tagModel->setData([
            'name' => $tag['name'],
            'url_key' => $tag['slug'],
        ]);

        return $tagModel;
    }

    /**
     * @param $data
     * @param $connection
     * @return void|null
     */
    protected function importCategories($data, $connection)
    {
        try {
            // @codingStandardsIgnoreStart
            $sqlString = "SELECT * FROM " . $this->prefix . "terms
                              INNER JOIN " . $this->prefix . "term_taxonomy
                              ON " . $this->prefix . "terms.term_id=" . $this->prefix . "term_taxonomy.term_id
                              WHERE " . $this->prefix . "term_taxonomy.taxonomy = 'category'
                              AND " . $this->prefix . "terms.name <> 'uncategorized' ORDER BY `parent` ASC";
            // @codingStandardsIgnoreEnd
            $result = $connection->query($sqlString);

            if (!$result) {
                return null;
            }

            while ($category = $result->fetch_assoc()) {
                if ($data['update']) {
                    $this->updateCategory($category);
                } else {
                    $this->saveCategory($category);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Amasty Import error with Categories: ') . $exception->getMessage());
        }
    }

    /**
     * @param $category
     */
    protected function updateCategory($category)
    {
        $categoryModel = $this->prepareCategoryData($category);
        $existCategory = $this->categoryRepository->getByUrlKey($categoryModel->getUrlKey());
        $categoryModel->setCategoryId($existCategory->getCategoryId());
        $this->categoryRepository->save($categoryModel);
        $this->importedCategories[$category['term_id']] = $categoryModel->getCategoryId();
    }

    /**
     * @param $category
     */
    protected function saveCategory($category)
    {
        $categoryModel = $this->prepareCategoryData($category);
        $existCategory = $this->categoryRepository->getByUrlKey($categoryModel->getUrlKey());

        if ($existCategory->getCategoryId()) {
            $categoryModel->setUrlKey($categoryModel->getUrlKey() . '-duplicate');
        }

        $this->categoryRepository->save($categoryModel);
        $this->importedCategories[$category['term_id']] = $categoryModel->getCategoryId();
    }

    /**
     * @param $category
     * @return \Amasty\Blog\Model\Categories
     */
    protected function prepareCategoryData($category)
    {
        $categoryModel = $this->categoryRepository->getCategory();
        $date = $this->dateTime->date();

        try {
            $parentCategory = $this->categoryRepository->getById($this->importedCategories[$category['parent']]);
        } catch (\Exception $exception) {
            $parentCategory = null;
        }

        $categoryData = [
            'name' => $category['name'],
            'url_key' => $category['slug'],
            'status' => 1,
            'sort_order' => 0,
            'created_at' => $date,
            'updated_at' => $date,
            'parent_id' => $this->getParentId($category),
            'path' => $this->getPath($category, $parentCategory),
            'level' => $this->getLevel($category, $parentCategory),
            'store_ids' => [0],
        ];

        $categoryData = array_merge($categoryData, $this->getCategoryMeta((int)$category['term_id']));

        $categoryModel->setData($categoryData);

        return $categoryModel;
    }

    private function getCategoryMeta(int $categoryId): array
    {
        $categoryMeta = [
            'meta_title' => '',
            'meta_description' => '',
        ];

        if (!$this->isMetaTaxonomyLoaded) {
            // @codingStandardsIgnoreStart
            $sqlString = "SELECT option_value FROM "
                . $this->prefix
                . "options WHERE option_name='wpseo_taxonomy_meta';";
            $result = $this->connection->query($sqlString);
            if (!$result) {
                return $categoryMeta;
            }

            try {
                $wpMeta = $result->fetch_assoc();
                $result = $this->serializer->unserialize($wpMeta['option_value']);
                $this->metaTaxonomy = $result;
            } catch (\Exception $e) {
                $this->metaTaxonomy = null;
            }

            $this->isMetaTaxonomyLoaded = true;
        }

        if ($this->metaTaxonomy === null) {
            return $categoryMeta;
        }

        if (isset($this->metaTaxonomy[$categoryId])) {
            $categoryMeta['meta_title'] = $this->metaTaxonomy[$categoryId]['wpseo_title'] ?? '';
            $categoryMeta['meta_description'] = $this->metaTaxonomy[$categoryId]['wpseo_desc'] ?? '';
        }

        return $categoryMeta;
    }

    /**
     * @param $category
     * @return int|mixed
     */
    protected function getParentId($category)
    {
        $parentId = 0;

        if ($category['parent'] !== '0') {
            $parentId = isset($this->importedCategories[$category['parent']])
                ? $this->importedCategories[$category['parent']]
                : 0;
        }

        return $parentId;
    }

    /**
     * @param $category
     * @param $parentCategory
     * @return int|string
     */
    protected function getPath($category, $parentCategory)
    {
        $path = 0;

        if ($category['parent'] !== '0' && $parentCategory) {
            $parentPath = $parentCategory->getPath();
            $parentPaths = explode('/', $parentPath);
            $path = $parentPath == '0' ? 1 : $parentPath . '/' . (end($parentPaths) + 1);
        }

        return $path;
    }

    /**
     * @param $category
     * @param $parentCategory
     * @return int
     */
    protected function getLevel($category, $parentCategory)
    {
        $level = 1;

        if ($category['parent'] !== '0' && $parentCategory) {
            $level = $parentCategory->getLevel() + 1;
        }

        return $level;
    }

    /**
     * @param $data
     * @param $connection
     * @return void|null
     */
    protected function importAuthors($data, $connection)
    {
        try {
            // @codingStandardsIgnoreStart
            $sqlString = "SELECT * FROM " . $this->prefix . "users";
            // @codingStandardsIgnoreEnd
            $result = $connection->query($sqlString);

            if (!$result) {
                return null;
            }

            while ($user = $result->fetch_assoc()) {
                if ($data['update']) {
                    $this->updateAuthor($user);
                } else {
                    $this->saveAuthor($user);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Amasty Import error with Authors: ') . $exception->getMessage());
        }
    }

    /**
     * @param $author
     */
    protected function updateAuthor($author)
    {
        $authorModel = $this->prepareAuthorData($author);
        $existAuthor = $this->authorRepository->getByUrlKey($authorModel->getUrlKey());
        $authorModel->setAuthorId($existAuthor->getAuthorId());
        $this->authorRepository->save($authorModel);
        $this->importedAuthors[$author['ID']] = $authorModel->getId();
    }

    /**
     * @param $author
     */
    protected function saveAuthor($author)
    {
        $authorModel = $this->prepareAuthorData($author);
        $existAuthor = $this->authorRepository->getByUrlKey($authorModel->getUrlKey());

        if ($existAuthor->getAuthorId()) {
            $authorModel->setUrlKey($authorModel->getUrlKey() . '-duplicate');
        }

        $this->authorRepository->save($authorModel);
        $this->importedAuthors[$author['ID']] = $authorModel->getAuthorId();
    }

    /**
     * @param $author
     * @return \Amasty\Blog\Model\Author
     */
    protected function prepareAuthorData($author)
    {
        $authorModel = $this->authorRepository->getAuthorModel();
        $description = $this->getAuthorDescription($author);
        $authorModel->setData([
            'name' => $author['display_name'],
            'url_key' => $author['user_login'],
            'image' => $this->getAuthorImage($author),
            'description' => $description,
            'short_description' => $description
        ]);

        return $authorModel;
    }

    private function getAuthorDescription(array $author): string
    {
        // @codingStandardsIgnoreStart
        $sqlString = "SELECT meta_value FROM "
            . $this->prefix
            . "usermeta WHERE meta_key='description' AND user_id=" . $author['ID'];
        $result = $this->connection->query($sqlString);

        if (!$result) {
            return '';
        }

        $result = $result->fetch_assoc();

        return !empty($result['meta_value']) ? (string)$result['meta_value'] : '';
    }

    private function getAuthorImage(array $author): string
    {
        // @codingStandardsIgnoreStart
        $sqlString = "SELECT meta_value FROM "
            . $this->prefix
            . "usermeta WHERE meta_key='wp_user_avatar' AND user_id="
            . $author['ID'];
        $result = $this->connection->query($sqlString);

        if (!$result) {
            return '';
        }

        $result = $result->fetch_assoc();
        if (empty($result['meta_value']) || $result['meta_value'] == 0) {
            return '';
        }

        $metaId = $result['meta_value'];

        $sqlString = "SELECT guid FROM " . $this->prefix . "posts WHERE ID=" . $metaId;
        $result = $this->connection->query($sqlString);

        if (!$result) {
            return '';
        }

        $imageUrl = $result->fetch_assoc();

        if (empty($imageUrl['guid'])) {
            return '';
        }

        $imageUrl = $imageUrl['guid'];

        $imageUrl = preg_replace(
            "/(https:\/\/)(.+?)(\/wp-content\/)/",
            "",
            $imageUrl
        );

        return preg_replace(
            "/(http:\/\/)(.+?)(\/wp-content\/)/",
            "",
            $imageUrl
        );
    }

    /**
     * @param $connection
     * @return void|null
     */
    protected function importComments($connection)
    {
        try {
            // @codingStandardsIgnoreStart
            $sqlString = "SELECT * FROM " . $this->prefix . "comments ORDER BY comment_parent ASC";
            // @codingStandardsIgnoreEnd
            $result = $connection->query($sqlString);

            if (!$result) {
                return null;
            }

            while ($comment = $result->fetch_assoc()) {
                $this->saveComment($comment);
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Amasty Import error with Comments: ') . $exception->getMessage());
        }
    }

    /**
     * @param $comment
     */
    protected function saveComment($comment)
    {
        $commentModel = $this->prepareCommentData($comment);

        if ($commentModel) {
            $this->commentRepository->save($commentModel);
            $this->importedComments[$comment['comment_ID']] = $commentModel->getCommentId();
        }
    }

    /**
     * @param $comment
     * @return \Amasty\Blog\Api\Data\CommentInterface
     */
    private function prepareCommentData($comment)
    {
        $postId = isset($this->importedPosts[$comment['comment_post_ID']])
            ? $this->importedPosts[$comment['comment_post_ID']]
            : 0;

        if (!$postId) {
            return false;
        }

        $commentModel = $this->commentRepository->getComment();
        $date = $this->dateTime->date();

        $commentModel->setData([
            'post_id' => $postId,
            'store_id' => 0,
            'status' => $this->getCommentStatus($comment['comment_approved']),
            'customer_id' => $comment['user_id'] && isset($this->importedAuthors[$comment['user_id']])
                ? $this->importedAuthors[$comment['user_id']]
                : null,
            'reply_to' => isset($this->importedComments[$comment['comment_parent']])
                ? $this->importedComments[$comment['comment_parent']]
                : null,
            'message' => $comment['comment_content'],
            'name' => $comment['comment_author'],
            'email' => $comment['comment_author_email'],
            'created_at' => $comment['comment_date_gmt'] ?: $date,
            'updated_at' => $date,
        ]);

        return $commentModel;
    }

    private function getCommentStatus(string $commentApproved = ''): int
    {
        $status = CommentStatus::STATUS_PENDING;
        switch ($commentApproved) {
            case '':
                break;
            case '1':
                $status = CommentStatus::STATUS_APPROVED;
                break;
            case self::SPAM_COMMENT:
                $status = CommentStatus::STATUS_REJECTED;
                break;
        }

        return $status;
    }
}
