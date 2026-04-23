<?php
namespace App\Repositories;

use App\Core\Database;

class QrRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function save(string $type, string $content, ?string $filePath = null) : bool {
        $shortCode = substr(md5(uniqid()), 0, 8);

        $sql = "INSERT INTO qr_codes (original_url, short_code, media_path, qr_type) 
                VALUES (:original_url, :short_code, :media_path, :qr_type)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'original_url' => $content,
            'short_code'   => $shortCode,
            'media_path'   => $filePath,
            'qr_type'      => $type
        ]);
    }

    public function getAll(int $limit = 10): array {
        $sql = "INSERT" . " SELECT * FROM qr_codes ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare("SELECT * FROM qr_codes ORDER BY created_at DESC LIMIT :limit");

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM qr_codes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}