<?php

require_once __DIR__ . '/BlockManager.php';

class Post
{
    protected $db;
    protected $blockManager;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->blockManager = new BlockManager($db);
    }

    // Helper function to get the content type ID for 'post'
    private function getPostContentTypeId()
    {
        $sql = "SELECT id FROM content_types WHERE name = 'post'";
        return $this->db->query($sql)->fetchColumn();
    }

    // Fetch the latest 10 posts
    public function getLatestPosts()
    {
        $sql = "SELECT contents.id, contents.title, contents.slug, blocks.content, blocks.type 
                FROM contents 
                INNER JOIN blocks ON contents.id = blocks.content_id 
                WHERE contents.content_type_id = (SELECT id FROM content_types WHERE name = 'post') 
                ORDER BY contents.created_at DESC, blocks.order_num ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        foreach ($results as $result) {
            $postId = $result['id'];
            if (!isset($posts[$postId])) {
                $posts[$postId] = [
                    'title' => $result['title'],
                    'slug' => $result['slug'],
                    'blocks' => [],
                ];
            }
            $posts[$postId]['blocks'][] = [
                'type' => $result['type'],
                'content' => $result['content'],
            ];
        }
        return array_values($posts);
    }

    // Fetch all posts
    public function getAllPosts()
    {
        $postTypeId = $this->getPostContentTypeId();
        $sql = "SELECT * FROM contents WHERE content_type_id = :postTypeId ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostDetailsById($id)
    {
        $sql = "SELECT * FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add a new post
    public function addPost($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId, $categoryId)
    {
        $slug = $this->generateSlug($title);
        $postTypeId = $this->getPostContentTypeId();
        $categoryId = (int) $categoryId;

        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO contents (content_type_id, title, subtitle, main_image_path, show_main_image, is_active, slug, user_id, category_id) 
            VALUES (:postTypeId, :title, :subtitle, :mainImagePath, :showMainImage, :isActive, :slug, :userId, :categoryId)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':subtitle', $subtitle, PDO::PARAM_STR);
            $stmt->bindParam(':mainImagePath', $mainImagePath, PDO::PARAM_STR);
            $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
            $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            $contentId = $this->db->lastInsertId();
            $this->blockManager->insertBlocksForContent($contentId, $contentBlocks);

            $this->db->commit();
        } catch (Exception $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }


    public function generateSlug($title)
    {
        $title = trim($title);
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $title));
        $originalSlug = $slug;
        $i = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists($slug)
    {
        $postTypeId = $this->getPostContentTypeId();
        $sql = "SELECT COUNT(*) FROM contents WHERE slug = :slug AND content_type_id = :postTypeId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Update an existing post by ID
    public function updatePost($id, $title, $contentBlocks, $slug, $userId, $subtitle, $mainImagePath, $showMainImage, $isActive, $categoryId)
    {
        $this->db->beginTransaction();

        try {
            $sql = "UPDATE contents SET 
            title = :title, 
            subtitle = :subtitle, 
            main_image_path = :mainImagePath, 
            show_main_image = :showMainImage, 
            is_active = :isActive, 
            user_id = :userId,
            category_id = :categoryId
        WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':subtitle' => $subtitle,
                ':mainImagePath' => $mainImagePath,
                ':showMainImage' => $showMainImage,
                ':isActive' => $isActive,
                ':userId' => $userId,
                ':categoryId' => $categoryId,
                ':id' => $id
            ]);

            $sqlSlug = "SELECT slug FROM contents WHERE id = :id";
            $stmtSlug = $this->db->prepare($sqlSlug);
            $stmtSlug->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtSlug->execute();
            $currentSlug = $stmtSlug->fetchColumn();

            if (empty($currentSlug)) {
                $sqlUpdateSlug = "UPDATE contents SET slug = :slug WHERE id = :id";
                $stmtUpdateSlug = $this->db->prepare($sqlUpdateSlug);
                $stmtUpdateSlug->bindParam(':slug', $slug, PDO::PARAM_STR);
                $stmtUpdateSlug->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtUpdateSlug->execute();
            }

            $this->blockManager->replaceBlocksForContent($id, $contentBlocks);

            $this->db->commit();
        } catch (Exception $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }




    public function getBlocksByPostId($postId)
    {
        $sql = "SELECT * FROM blocks WHERE content_id = :postId ORDER BY order_num ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postId', $postId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a post by its ID
    public function getPostById($id)
    {
        $sql = "SELECT contents.title AS content_title, contents.subtitle, 
            contents.main_image_path, contents.show_main_image, 
            contents.is_active, contents.slug, contents.category_id,
            categories.name AS category_name, categories.slug AS category_slug,
            blocks.* 
            FROM contents 
            LEFT JOIN blocks ON contents.id = blocks.content_id
            LEFT JOIN categories ON contents.category_id = categories.id
            WHERE contents.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        if (!empty($results)) {
            $postId = $results[0]['id'];
            $posts[$postId] = [
                'title' => $results[0]['content_title'],
                'subtitle' => $results[0]['subtitle'],
                'main_image_path' => $results[0]['main_image_path'],
                'show_main_image' => $results[0]['show_main_image'],
                'is_active' => $results[0]['is_active'],
                'slug' => $results[0]['slug'],
                'category_id' => $results[0]['category_id'],
                'category_name' => $results[0]['category_name'],
                'category_slug' => $results[0]['category_slug'],
                'blocks' => [],
            ];

            foreach ($results as $result) {
                $posts[$postId]['blocks'][] = [
                    'type' => $result['type'],
                    'content' => $result['content'],
                    'block_data' => $result
                ];
            }
        }

        return array_values($posts);
    }


    // Fetch a post by its slug
    public function getPostBySlug($slug)
    {
        $sql = "SELECT * FROM contents WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch a post by its category slug and post slug
    public function getPostByCategoryAndSlug($categorySlug, $postSlug)
    {
        $sql = "SELECT contents.*, categories.name AS category_name, categories.slug AS category_slug 
            FROM contents 
            INNER JOIN categories ON contents.category_id = categories.id 
            WHERE categories.slug = :categorySlug AND contents.slug = :postSlug";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categorySlug', $categorySlug, PDO::PARAM_STR);
        $stmt->bindParam(':postSlug', $postSlug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a post by its ID
    public function deletePost($id)
    {
        $this->db->beginTransaction();

        try {
            $this->blockManager->deleteBlocksByContentId($id);

            $sql = "DELETE FROM contents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            $this->db->commit();

            return $result;
        } catch (Exception $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function getPostsByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM contents 
                WHERE category_id = :categoryId AND content_type_id = (SELECT id FROM content_types WHERE name = 'post')
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorySlugByPostId($postId)
    {
        $sql = "SELECT categories.slug FROM contents
            JOIN categories ON contents.category_id = categories.id
            WHERE contents.id = :postId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['slug'] ?? null;
    }

    public function countPosts()
    {
        $sql = "SELECT COUNT(*) FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'post')";
        return $this->db->query($sql)->fetchColumn();
    }
}
