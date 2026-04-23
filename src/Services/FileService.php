<?php
namespace App\Services;
use Exception;

class FileService {
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'mp4', 'mov'];
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';

    public function upload(array $file): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload failed: " . $file['error']);
        }

        $extention = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extention, self::ALLOWED_EXTENSIONS)) {
            throw new Exception("Формат .$extention не підтримується.");
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0777, true);
        }

        $newFileName = uniqid('qr_file_', true) . '.' . $extention;
        $destination = self::UPLOAD_DIR . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Не вдалося зберегти файл.");
        }

        return $destination;
    }
}