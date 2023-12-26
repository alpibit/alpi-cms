<?php

class Page
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Helper function to get the content type ID for 'page'
    private function getPageContentTypeId()
    {
        $sql = "SELECT id FROM content_types WHERE name = 'page'";
        return $this->db->query($sql)->fetchColumn();
    }

    // Fetch all pages
    public function getAllPages()
    {
        $pageTypeId = $this->getPageContentTypeId();
        $sql = "SELECT * FROM contents WHERE content_type_id = :pageTypeId ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pageTypeId', $pageTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a page by its ID
    public function getPageById($id)
    {
        $sql = "SELECT * FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch a page by its slug
    public function getPageBySlug($slug)
    {
        $sql = "SELECT * FROM contents WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePage($id, $title, $contentBlocks, $userId)
    {
        // Updating contents table
        $sql = "UPDATE contents SET title = :title, user_id = :userId WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Delete existing blocks for this page
        $sqlDelete = "DELETE FROM blocks WHERE content_id = :id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Inserting updated blocks for this page
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

    // Get blocks by page ID
    public function getBlocksByPageId($pageId)
    {
        $sql = "SELECT * FROM blocks WHERE content_id = :pageId ORDER BY order_num ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
