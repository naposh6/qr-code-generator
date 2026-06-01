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

$type    = $_POST['type'] ?? 'url';
$content = $_POST['content'] ?? '';
$userId  = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Сесія закінчилася. Будь ласка, авторизуйтесь знову.']);
    exit;
}

try {
    $qrService   = new QrGeneratorService();
    $fileService = new FileService();
    $qrRepo      = new QrRepository();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
        throw new \Exception("Файл занадто великий для сервера.");
    }

    $finalData   = '';
    $factoryData = null;
    $title       = $_POST['title'] ?? null;

    // ── Customisation options ──────────────────────────────────────────────
    $options = [
        'color'      => $_POST['qr_color']    ?? '#000000',
        'bg_color'   => $_POST['bg_color']    ?? '#ffffff',
        'size'       => (int)($_POST['qr_size'] ?? 400),
        'qr_style'   => $_POST['qr_style']    ?? 'square',   // dot shape
        'eye_outer'  => $_POST['eye_outer']   ?? 'square',   // outer eye border
        'eye_inner'  => $_POST['eye_inner']   ?? 'square',   // inner eye dot
        'margin'     => max(0, min(4, (int)($_POST['margin'] ?? 1))),
        'logo_path'  => null,
    ];

    // ── Logo upload ────────────────────────────────────────────────────────
    if (isset($_FILES['qr_logo']) && $_FILES['qr_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadedLogo         = $fileService->upload($_FILES['qr_logo'], 'image');
        $options['logo_path'] = __DIR__ . '/../public/' . $uploadedLogo;
    }

    // ── Content parsing ────────────────────────────────────────────────────
    if (in_array($type, ['image', 'video'])) {
        if (!isset($_FILES['qr_file']) || $_FILES['qr_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Будь ласка, завантажте файл.");
        }
        $uploadedFilePath = $fileService->upload($_FILES['qr_file'], $type);
        $finalData        = 'http://localhost/QR-code%20generator/public/' . $uploadedFilePath;

    } elseif ($type === 'pdf') {
        if (!isset($_FILES['qr_file']) || $_FILES['qr_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Будь ласка, оберіть PDF.");
        }
        $uploadedFilePath = $fileService->upload($_FILES['qr_file'], 'image');
        $finalData        = 'http://localhost/QR-code%20generator/public/' . $uploadedFilePath;

    } elseif ($type === 'wifi') {
        $ssid = trim($_POST['wifi_ssid'] ?? '');
        $pass = $_POST['wifi_password'] ?? '';
        $enc  = 'WPA';
        if (empty($ssid)) throw new \Exception("Введіть назву Wi-Fi мережі (SSID).");
        $finalData   = "WIFI:T:{$enc};S:{$ssid};P:{$pass};;";
        $factoryData = json_encode(['ssid' => $ssid, 'password' => $pass, 'encryption' => $enc]);

    } elseif ($type === 'call') {
        $phone = trim($_POST['call_phone'] ?? '');
        if (empty($phone)) throw new \Exception("Введіть номер телефону.");
        $finalData   = "tel:{$phone}";
        $factoryData = json_encode(['phone' => $phone]);

    } elseif ($type === 'vcard') {
        $name  = trim($_POST['vcard_name']  ?? '');
        $phone = trim($_POST['vcard_phone'] ?? '');
        if (empty($name) || empty($phone)) throw new \Exception("Заповніть ім'я та номер телефону контакту.");
        $finalData   = "BEGIN:VCARD\nVERSION:3.0\nFN:{$name}\nTEL:{$phone}\nEND:VCARD";
        $factoryData = json_encode(['name' => $name, 'phone' => $phone]);

    } else {
        $finalData = $content;
    }

    if (empty($finalData)) {
        throw new \Exception("Вміст не може бути порожнім.");
    }

    $qrContent = QrContentFactory::create($type, $factoryData ?? $finalData);

    // ── Save paths ─────────────────────────────────────────────────────────
    $fileName     = 'qr_' . uniqid() . '.png';
    $relativePath = 'uploads/qr/' . $fileName;
    $fullSavePath = __DIR__ . '/../public/' . $relativePath;

    if (!is_dir(dirname($fullSavePath))) {
        mkdir(dirname($fullSavePath), 0777, true);
    }

    // ── Generate ───────────────────────────────────────────────────────────
    $result = $qrService->generate($qrContent, $fullSavePath, $options);

    $qrRepo->save($type, $finalData, $userId ? (int)$userId : null, $relativePath, $title);

    echo json_encode([
        'success'    => true,
        'media_path' => $relativePath,
        'svg'        => $result['svg'],          // for live preview in browser
        'png_uri'    => $result['png_data_uri'], // for direct download fallback
    ]);
    exit;

} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'PHP Помилка: ' . $e->getMessage() . ' у файлі ' . basename($e->getFile()) . ' на рядку ' . $e->getLine(),
    ]);
    exit;
}