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
}