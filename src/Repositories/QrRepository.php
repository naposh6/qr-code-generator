<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class QrRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function save(string $type, string $content, ?int $userId, ?string $filePath = null, ?string $title = null) : bool {
        $shortCode = substr(md5(uniqid()), 0, 8);

        $sql = "INSERT INTO qr_codes (original_url, short_code, media_path, qr_type, user_id, title) 
            VALUES (:original_url, :short_code, :media_path, :qr_type, :user_id, :title)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'original_url' => $content,
            'short_code'   => $shortCode,
            'media_path'   => $filePath,
            'qr_type'      => $type,
            'user_id'      => $userId,
            'title'        => $title
        ]);
    }

    public function registerScan(int $qrId, string $ip, string $userAgent): bool {
        $sql = "INSERT INTO scans (qr_code_id, ip_address, user_agent, scanned_at) 
            VALUES (:qr_id, :ip, :ua, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'qr_id' => $qrId,
            'ip'    => $ip,
            'ua'    => $userAgent
        ]);
    }

    public function getByUserId(int $userId, int $limit = 10): array {
        $sql = "SELECT * FROM qr_codes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT * FROM qr_codes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getAll(int $limit = 10): array {
        $sql = "SELECT * FROM qr_codes ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM qr_codes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteMultiple(array $ids): bool {
        if (empty($ids)) return false;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "DELETE FROM qr_codes WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($ids);
    }
}