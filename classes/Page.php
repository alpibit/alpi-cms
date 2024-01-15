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
        $sql = "SELECT contents.title AS content_title, contents.subtitle, contents.main_image_path, 
                   contents.show_main_image, contents.is_active, contents.slug, blocks.*
            FROM contents
            LEFT JOIN blocks ON contents.id = blocks.content_id 
            WHERE contents.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page = [];
        if (!empty($results)) {
            $pageId = $results[0]['id'];
            $page = [
                'title' => $results[0]['content_title'],
                'subtitle' => $results[0]['subtitle'],
                'main_image_path' => $results[0]['main_image_path'],
                'show_main_image' => $results[0]['show_main_image'],
                'is_active' => $results[0]['is_active'],
                'slug' => $results[0]['slug'],
                'blocks' => [],
            ];

            foreach ($results as $result) {
                if (isset($result['type'])) {
                    $page['blocks'][] = [
                        'id' => $result['id'],
                        'type' => $result['type'],
                        'title' => $result['title'],
                        'content' => $result['content'],
                        'block_data' => $result
                    ];
                }
            }
        }
        return $page;
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

    public function updatePage($id, $title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId)
    {
        // Updating contents table with new fields
        $sql = "UPDATE contents SET title = :title, subtitle = :subtitle, main_image_path = :mainImagePath, show_main_image = :showMainImage, is_active = :isActive, user_id = :userId WHERE id = :id";
        $stmt = $this->db->prepare($sql);


        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':subtitle', $subtitle);
        $stmt->bindParam(':mainImagePath', $mainImagePath);
        $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
        $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Delete existing blocks for this page
        $sqlDelete = "DELETE FROM blocks WHERE content_id = :id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Inserting updated blocks for this page
        foreach ($contentBlocks as $index => $block) {
            if (!empty($block['type'])) {
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

    public function addPage($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId)
    {
        $slug = $this->generateSlug($title);
        $pageTypeId = $this->getPageContentTypeId();

        $sql = "INSERT INTO contents (content_type_id, title, subtitle, main_image_path, show_main_image, is_active, slug, user_id) 
            VALUES (:pageTypeId, :title, :subtitle, :mainImagePath, :showMainImage, :isActive, :slug, :userId)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pageTypeId', $pageTypeId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':subtitle', $subtitle, PDO::PARAM_STR);
        $stmt->bindParam(':mainImagePath', $mainImagePath, PDO::PARAM_STR);
        $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
        $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $contentId = $this->db->lastInsertId();

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

    public function deletePage($id)
    {
        if ($id == 1) {
            return false;
        }
        $sqlBlocks = "DELETE FROM blocks WHERE content_id = :id";
        $stmtBlocks = $this->db->prepare($sqlBlocks);
        $stmtBlocks->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtBlocks->execute();

        $sql = "DELETE FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
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
        $postTypeId = $this->getPageContentTypeId();
        $sql = "SELECT COUNT(*) FROM contents WHERE slug = :slug AND content_type_id = :postTypeId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function countPages()
    {
        $sql = "SELECT COUNT(*) FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'page')";
        return $this->db->query($sql)->fetchColumn();
    }
}
