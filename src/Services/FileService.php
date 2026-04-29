<?php
namespace App\Services;
use Exception;

class FileService {
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'mp4', 'mov'];
    private const BASE_UPLOAD_DIR = __DIR__ . '/../../public/uploads/';

    public function upload(array $file, string $type): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Помилка завантаження: " . $file['error']);
        }

        $extention = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extention, self::ALLOWED_EXTENSIONS)) {
            throw new Exception("Формат .$extention не підтримується.");
        }

        $subDir = ($type === 'video') ? 'videos/' : 'images/';
        $finalDir = self::BASE_UPLOAD_DIR . $subDir;

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0777, true);
        }

        $newFileName = uniqid('file_', true) . '.' . $extention;
        $destination = $finalDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Не вдалося зберегти файл.");
        }

        return 'uploads/' . $subDir . $newFileName;
    }

    public function deleteFile(string $relativePath): bool {
        $fullPath = __DIR__ . '/../../public/' . $relativePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath); // Видаляємо файл
        }

        return false;
    }
}