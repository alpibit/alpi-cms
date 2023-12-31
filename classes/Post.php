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

        // Inserting blocks related to this post
        foreach ($contentBlocks as $index => $block) {
            $orderNum = $index + 1;
            $sqlBlock = "INSERT INTO blocks (content_id, type, title, content, selected_post_ids, image_path, alt_text, caption, url1, cta_text1, url2, cta_text2, style1, style2, style3, style4, style5, style6, style7, style8, background_color, order_num) 
                     VALUES (:contentId, :type, :title, :content, :selectedPostIds, :imagePath, :altText, :caption, :url1, :ctaText1, :url2, :ctaText2, :style1, :style2, :style3, :style4, :style5, :style6, :style7, :style8, :backgroundColor, :orderNum)";

            $stmtBlock = $this->db->prepare($sqlBlock);
            $stmtBlock->bindParam(':contentId', $contentId, PDO::PARAM_INT);
            $stmtBlock->bindParam(':type', $block['type'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':title', $block['title'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':content', $block['content'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':selectedPostIds', $block['selected_post_ids'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':imagePath', $block['image_path'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':altText', $block['alt_text'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':caption', $block['caption'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':url1', $block['url1'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':ctaText1', $block['cta_text1'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':url2', $block['url2'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':ctaText2', $block['cta_text2'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style1', $block['style1'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style2', $block['style2'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style3', $block['style3'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style4', $block['style4'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style5', $block['style5'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style6', $block['style6'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style7', $block['style7'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':style8', $block['style8'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':backgroundColor', $block['background_color'], PDO::PARAM_STR);
            $stmtBlock->bindParam(':orderNum', $orderNum, PDO::PARAM_INT);
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
    public function updatePost($id, $title, $contentBlocks, $slug, $userId, $subtitle, $mainImagePath, $showMainImage, $isActive, $categoryId)
    {
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
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(
            ':subtitle',
            $subtitle,
            PDO::PARAM_STR
        );
        $stmt->bindParam(':mainImagePath', $mainImagePath, PDO::PARAM_STR);
        $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
        $stmt->bindParam(
            ':isActive',
            $isActive,
            PDO::PARAM_BOOL
        );
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        // Delete existing blocks for this post
        $sqlDelete = "DELETE FROM blocks WHERE content_id = :id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Inserting updated blocks for this post
        if (!empty($contentBlocks)) {
            foreach ($contentBlocks as $index => $block) {
                $orderNum = $index + 1;
                $sqlBlock = "INSERT INTO blocks (content_id, type, title, content, selected_post_ids, image_path, alt_text, caption, url1, cta_text1, url2, cta_text2, style1, style2, style3, style4, style5, style6, style7, style8, background_color, order_num) 
                         VALUES (:contentId, :type, :title, :content, :selectedPostIds, :imagePath, :altText, :caption, :url1, :ctaText1, :url2, :ctaText2, :style1, :style2, :style3, :style4, :style5, :style6, :style7, :style8, :backgroundColor, :orderNum)";

                $stmtBlock = $this->db->prepare($sqlBlock);
                $stmtBlock->bindParam(':contentId', $id, PDO::PARAM_INT);
                $stmtBlock->bindParam(':type', $block['type'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':title', $block['title'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':content', $block['content'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':selectedPostIds', $block['selected_post_ids'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':imagePath', $block['image_path'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':altText', $block['alt_text'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':caption', $block['caption'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':url1', $block['url1'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':ctaText1', $block['cta_text1'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':url2', $block['url2'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':ctaText2', $block['cta_text2'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style1', $block['style1'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style2', $block['style2'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style3', $block['style3'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style4', $block['style4'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style5', $block['style5'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style6', $block['style6'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style7', $block['style7'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':style8', $block['style8'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':backgroundColor', $block['background_color'], PDO::PARAM_STR);
                $stmtBlock->bindParam(':orderNum', $orderNum, PDO::PARAM_INT);
                $stmtBlock->execute();
            }
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
}
