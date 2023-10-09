<?php

class Post
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
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

    // Add a new post
    public function addPost($title, $contentBlocks, $userId)
    {
        $slug = $this->generateSlug($title);
        $postTypeId = $this->getPostContentTypeId();

        // Inserting into contents table
        $sql = "INSERT INTO contents (content_type_id, title, slug, user_id) VALUES (:postTypeId, :title, :slug, :userId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $contentId = $this->db->lastInsertId();

        // Inserting blocks related to this post
        foreach ($contentBlocks as $index => $block) {
            $orderNum = $index + 1;  // This will set order_num starting from 1, 2, 3,...
            $sqlBlock = "INSERT INTO blocks (content_id, type, content, order_num) VALUES (:contentId, :type, :content, :orderNum)";
            $stmtBlock = $this->db->prepare($sqlBlock);
            $stmtBlock->bindParam(':contentId', $contentId, PDO::PARAM_INT);
            $stmtBlock->bindParam(':type', $block['type'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':content', $block['content'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':orderNum', $orderNum, PDO::PARAM_INT); // Bind orderNum
            $stmtBlock->execute();
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
    public function updatePost($id, $title, $contentBlocks, $slug, $userId)
    {
        // Updating contents table
        $sql = "UPDATE contents SET title = :title, slug = :slug, user_id = :userId WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Delete existing blocks for this post
        $sqlDelete = "DELETE FROM blocks WHERE content_id = :id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Inserting updated blocks for this post
        foreach ($contentBlocks as $block) {
            $sqlBlock = "INSERT INTO blocks (content_id, type, content) VALUES (:id, :type, :content)";
            $stmtBlock = $this->db->prepare($sqlBlock);
            $stmtBlock->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtBlock->bindParam(':type', $block['type'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':content', $block['content'], PDO::PARAM_STR);
            $stmtBlock->execute();
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
        $sql = "SELECT contents.*, blocks.type, blocks.content FROM contents 
                LEFT JOIN blocks ON contents.id = blocks.content_id 
                WHERE contents.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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

    // Fetch a post by its slug
    public function getPostBySlug($slug)
    {
        $sql = "SELECT * FROM contents WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a post by its ID
    public function deletePost($id)
    {
        // Delete associated blocks
        $sqlBlocks = "DELETE FROM blocks WHERE content_id = :id";
        $stmtBlocks = $this->db->prepare($sqlBlocks);
        $stmtBlocks->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtBlocks->execute();

        // Delete the post
        $sql = "DELETE FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
