<?php

class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Authenticate user
    public function authenticate($username, $password)
    {
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
    public function getRole($username)
    {
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

    public function getUserData($username)
    {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registerUser($username, $password, $email, $role = 'editor')
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, email, role) VALUES (:username, :hashedPassword, :email, :role)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':hashedPassword', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);

        $stmt->execute();
    }

    public function updateUser($username, $password = null, $email = null)
    {
        $params = [':username' => $username];

        $query = "UPDATE users SET";

        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query .= " password = :password,";
            $params[':password'] = $hashedPassword;
        }

        if ($email) {
            $query .= " email = :email,";
            $params[':email'] = $email;
        }

        // Remove trailing comma
        $query = rtrim($query, ',');

        $query .= " WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
    }

    public function deleteUser($username)
    {
        $query = "DELETE FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        return $stmt->execute();
    }
}
