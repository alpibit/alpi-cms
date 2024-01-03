<?php

class Category
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Fetch all categories
    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a category by its ID
    public function getCategoryById($id)
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add a new category
    public function addCategory($name)
    {
        $slug = $this->generateSlug($name);

        $originalSlug = $slug;
        $i = 1;
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        $sql = "INSERT INTO categories (name, slug) VALUES (:name, :slug)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    // Update an existing category by ID
    public function updateCategory($id, $name)
    {
        $slug = $this->generateSlug($name);

        $originalSlug = $slug;
        $i = 1;
        while ($this->slugExists($slug) && !$this->isSlugBelongToCategory($id, $slug)) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        $sql = "UPDATE categories SET name = :name, slug = :slug WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }

    // Check if a slug belongs to a category
    private function isSlugBelongToCategory($categoryId, $slug)
    {
        $sql = "SELECT id FROM categories WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $existingId = $stmt->fetchColumn();

        return $existingId == $categoryId;
    }

    // Delete a category by its ID
    public function deleteCategory($id)
    {
        if ($id == 1) {
            return false;
        }

        // Transfer posts from the deleted category to the General category
        $transferPostsSql = "UPDATE contents SET category_id = 1 WHERE category_id = :categoryId";
        $transferPostsStmt = $this->db->prepare($transferPostsSql);
        $transferPostsStmt->bindParam(':categoryId', $id, PDO::PARAM_INT);
        $transferPostsStmt->execute();

        // Proceed to delete the category
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Generate a URL-friendly slug from a string
    private function generateSlug($string)
    {
        $string = trim($string);
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    // Check if a slug already exists (to avoid duplicates)
    private function slugExists($slug)
    {
        $sql = "SELECT COUNT(*) FROM categories WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Check if a category exists
    public function categoryExists($slug)
    {
        $sql = "SELECT COUNT(*) FROM categories WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Fetch a category by its slug
    public function getCategoryBySlug($slug)
    {
        $sql = "SELECT * FROM categories WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
