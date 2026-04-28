<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(["email" => $email]);
        return $stmt->fetch();
    }

    public function register(string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (email, password, role) VALUES (:email, :password, 'user')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(["email" => $email, "password" => $hash]);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM users WHERE id = :id AND role != 'admin'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAllUsers(): array {
        $sql = "SELECT id, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateRole(int $userId, string $role): bool {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updatePassword(int $userId, string $hashedPassword): bool {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}