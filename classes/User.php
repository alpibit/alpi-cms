<?php

trait PasswordHandler
{
    private $minLength = 12;
    private $passwordErrors = [];

    public function validatePassword($password)
    {
        $this->passwordErrors = [];

        if (strlen($password) < $this->minLength) {
            $this->passwordErrors[] = "Password must be at least {$this->minLength} characters long";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $this->passwordErrors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $this->passwordErrors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $this->passwordErrors[] = "Password must contain at least one number";
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->passwordErrors[] = "Password must contain at least one special character";
        }

        if ($this->isCommonPassword($password)) {
            $this->passwordErrors[] = "This password is too common. Please choose a stronger password";
        }

        return empty($this->passwordErrors);
    }

    public function getPasswordErrors()
    {
        return $this->passwordErrors;
    }

    private function isCommonPassword($password)
    {
        $commonPasswords = [
            'password123',
            'admin123',
            '123456789',
            'qwerty123',
            'letmein123',
            'welcome123',
            'monkey123',
            'football123',
            'abc123456',
            'password1',
            '12345678',
            'qwerty123456'
        ];
        return in_array(strtolower($password), $commonPasswords);
    }

    protected function hashPassword($password)
    {
        $options = [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 2
        ];

        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }
}

class User
{
    use PasswordHandler;

    private $conn;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function authenticate($username, $password)
    {
        if (!$this->checkLoginAttempts($username)) {
            throw new Exception("Account temporarily locked due to too many failed attempts. Please try again later.");
        }

        $query = "SELECT password, login_attempts, last_attempt FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $row['password'])) {
                $this->resetLoginAttempts($username);

                if (password_needs_rehash($row['password'], PASSWORD_ARGON2ID)) {
                    $this->updateUser($username, $password);
                }

                return true;
            }

            $this->incrementLoginAttempts($username);
        }

        return false;
    }

    private function checkLoginAttempts($username)
    {
        $query = "SELECT login_attempts, last_attempt FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['login_attempts'] >= $this->maxLoginAttempts) {
                $lockoutTime = strtotime($row['last_attempt']) + $this->lockoutDuration;
                if (time() < $lockoutTime) {
                    return false;
                }

                $this->resetLoginAttempts($username);
            }
        }
        return true;
    }

    private function incrementLoginAttempts($username)
    {
        $query = "UPDATE users SET login_attempts = login_attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
    }

    private function resetLoginAttempts($username)
    {
        $query = "UPDATE users SET login_attempts = 0, last_attempt = NULL WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
    }

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
        if (!$this->validatePassword($password)) {
            throw new Exception(implode(", ", $this->getPasswordErrors()));
        }

        $hashedPassword = $this->hashPassword($password);

        $query = "INSERT INTO users (username, password, email, role, login_attempts) 
                 VALUES (:username, :hashedPassword, :email, :role, 0)";

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
        $updates = [];

        if ($password !== null) {
            if (!$this->validatePassword($password)) {
                throw new Exception(implode(", ", $this->getPasswordErrors()));
            }
            $hashedPassword = $this->hashPassword($password);
            $updates[] = "password = :password";
            $updates[] = "password_changed_at = CURRENT_TIMESTAMP";
            $params[':password'] = $hashedPassword;
        }

        if ($email !== null) {
            $updates[] = "email = :email";
            $params[':email'] = $email;
        }

        if (empty($updates)) {
            return;
        }

        $query .= " " . implode(", ", $updates);
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
