<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

use App\Factories\QrContentFactory;
use App\Services\QrGeneratorService;
use App\Services\FileService;
use App\Repositories\QrRepository;

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

$type = $_POST['type'] ?? 'url';
$content = $_POST['content'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Сесія закінчилася. Будь ласка, авторизуйтесь знову.']);
    exit;
}

try {
    $qrService = new QrGeneratorService();
    $fileService = new FileService();
    $qrRepo = new QrRepository();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
        throw new \Exception("Файл занадто великий для сервера.");
    }

    $finalData = '';
    $title = $_POST['title'] ?? null;

    $options = [
            'color' => $_POST['qr_color'] ?? '#000000',
            'bg_color' => $_POST['bg_color'] ?? '#ffffff',
            'size'  => (int)($_POST['qr_size'] ?? 400),
            'qr_style' => $_POST['qr_style'] ?? 'square',
            'logo_path' => null
    ];

    if (isset($_FILES['qr_logo']) && $_FILES['qr_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadedLogo = $fileService->upload($_FILES['qr_logo'], 'logo');
        $options['logo_path'] = __DIR__ . '/../public/' . $uploadedLogo;
    }

    if (in_array($type, ['image', 'video'])) {
        if (!isset($_FILES['qr_file']) || $_FILES['qr_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Будь ласка, завантажте файл.");
        }
        $uploadedFilePath = $fileService->upload($_FILES['qr_file'], $type);
        $finalData = 'http://localhost/QR-code%20generator/public/' . $uploadedFilePath;
    } elseif ($type === 'pdf') {
        if (!isset($_FILES['qr_file']) || $_FILES['qr_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Будь ласка, оберіть PDF.");
        }
        $uploadedFilePath = $fileService->upload($_FILES['qr_file'], 'image');
        $finalData = 'http://localhost/QR-code%20generator/public/' . $uploadedFilePath;
    } elseif ($type === 'wifi') {
        $ssid = $_POST['wifi_ssid'] ?? '';
        $pass = $_POST['wifi_password'] ?? '';
        $finalData = "WIFI:S:{$ssid};T:WPA;P:{$pass};;";
    } else {
        $finalData = $content;
    }

    if (empty($finalData)) {
        throw new \Exception("Вміст не може бути порожнім.");
    }

    $qrContent = QrContentFactory::create($type, $finalData, $_POST);

    $fileName = 'qr_' . uniqid() . '.png';
    $relativePath = 'uploads/qr/' . $fileName;
    $fullSavePath = __DIR__ . '/../public/' . $relativePath;

    if (!is_dir(dirname($fullSavePath))) {
        mkdir(dirname($fullSavePath), 0777, true);
    }

    $qrService->generate($qrContent, $fullSavePath, $options);

    $qrData = [
            'user_id' => $userId,
            'qr_type' => $type,
            'title' => $title,
            'original_url' => $finalData,
            'media_path' => $relativePath,
            'created_at' => date('Y-m-d H:i:s')
    ];

    $qrRepo->save($type, $finalData, $userId ? (int)$userId : null, $relativePath, $title);

    echo json_encode([
            'success' => true,
            'media_path' => $relativePath
    ]);
    exit;

} catch (\Throwable $e) {
    echo json_encode([
            'success' => false,
            'message' => 'PHP Помилка: ' . $e->getMessage() . ' у файлі ' . basename($e->getFile()) . ' на рядку ' . $e->getLine()
    ]);
    exit;
}