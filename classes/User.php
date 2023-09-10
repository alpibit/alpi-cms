<?php

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Authenticate user
    public function authenticate($username, $password) {
        $query = "SELECT password FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return password_verify($password, $row['password']);
        }
        
        return false;
    }

    // Get role of a user
    public function getRole($username) {
        $query = "SELECT role FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['role'];
        }
        
        return null;
    }

    // Additional methods will go here
}

