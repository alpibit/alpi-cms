<?php

class Post
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Fetch the latest 10 posts
    public function getLatestPosts()
    {
        $sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 10";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all posts
    public function getAllPosts()
    {
        $sql = "SELECT * FROM posts ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a new post
    public function addPost($title, $content)
    {
        $sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
    }

    // Update an existing post by ID
    public function updatePost($id, $title, $content)
    {
        $sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Fetch a post by its ID
    public function getPostById($id)
    {
        $sql = "SELECT * FROM posts WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a post by its ID
    public function deletePost($id)
    {
        $sql = "DELETE FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute(); // This will return a boolean indicating success or failure.
    }
}
