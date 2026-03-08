<?php

require_once __DIR__ . '/BlockManager.php';

class Page
{
    protected $db;
    protected $blockManager;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->blockManager = new BlockManager($db);
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
        $sql = "SELECT id, title, subtitle, main_image_path, show_main_image, is_active, slug FROM contents WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePage($id, $title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId)
    {
        $this->db->beginTransaction();

        try {
            $sql = "UPDATE contents SET 
                title = :title, 
                subtitle = :subtitle, 
                main_image_path = :mainImagePath, 
                show_main_image = :showMainImage, 
                is_active = :isActive, 
                user_id = :userId
            WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->bindParam(':mainImagePath', $mainImagePath);
            $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
            $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->blockManager->replaceBlocksForContent($id, $contentBlocks);

            $this->db->commit();
        } catch (Exception $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }








    public function addPage($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId)
    {
        $slug = $this->generateSlug($title);
        $pageTypeId = $this->getPageContentTypeId();

        $this->db->beginTransaction();

        try {
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
            $this->blockManager->insertBlocksForContent($contentId, $contentBlocks);

            $this->db->commit();
        } catch (Exception $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function getBlocksByPageId($pageId)
    {
        $sql = "SELECT * FROM blocks WHERE content_id = :pageId ORDER BY order_num ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pageId', $pageId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePage($id)
    {
        if ($id == 1) {
            return false;
        }

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
