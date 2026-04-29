<?php
namespace App\Services;
use Exception;

class FileService {
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'mp4', 'mov'];
    private const BASE_UPLOAD_DIR = __DIR__ . '/../../public/uploads/';

    public function upload(array $file, string $type): string {

        $maxSize = 10 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            throw new \Exception("Файл занадто великий. Максимум 10 МБ.");
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Помилка завантаження: " . $file['error']);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'image' => ['image/jpeg', 'image/png', 'image/gif'],
            'video' => ['video/mp4', 'video/quicktime']
        ];

        $checkGroup = ($type === 'video') ? 'video' : 'image';
        if (!in_array($mimeType, $allowedMimes[$checkGroup])) {
            throw new Exception("Недопустимий контент файлу ($mimeType).");
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
            return unlink($fullPath);
        }

        return false;
    }
}