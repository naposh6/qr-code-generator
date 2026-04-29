<?php

use App\Factories\QrContentFactory;
use App\Services\QrGeneratorService;
use App\Services\FileService;
use App\Repositories\QrRepository;

$type = $_POST['type'] ?? 'url';
$content = $_POST['content'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

$qrService = new QrGeneratorService();
$fileService = new FileService();
$qrRepo = new QrRepository();

$error = null;
$qrImageBase64 = '';
$displayContent = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
        throw new Exception("Файл занадто великий для сервера. Максимальний ліміт: " . ini_get('upload_max_filesize'));
    }

    $finalData = '';

    $title = $_POST['title'] ?? null;
    $options = [
            'color' => $_POST['qr_color'] ?? '#000000',
            'bg_color' => $_POST['bg_color'] ?? '#ffffff',
            'size'  => (int)($_POST['qr_size'] ?? 400),
            'qr_style' => $_POST['qr_style'] ?? 'square', // ДОДАЙ ЦЕ
            'logo_path' => null
    ];

    if (isset($_FILES['qr_logo']) && $_FILES['qr_logo']['error'] === UPLOAD_ERR_OK) {
        $options['logo_path'] = $_FILES['qr_logo']['tmp_name'];
    }

    if ($type === 'image' || $type === 'video') {
        if (!isset($_FILES['qr_file']) || $_FILES['qr_file']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Будь ласка, виберіть файл.");
        }
        $relativePath = $fileService->upload($_FILES['qr_file'], $type);

        $baseUrl = "http://localhost/QR-code generator/public/";
        $finalData = $baseUrl . $relativePath;
    } else {
        $finalData = $_POST['content'] ?? '';
        if (empty(trim($finalData))) {
            throw new Exception("Контент не може бути порожнім.");
        }
    }

    $qrContent = QrContentFactory::create($type, $finalData);
    $displayContent = $qrContent->getContent();

    $projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/QR-code generator/public/';
    $qrDir = 'uploads/qr/';

    if (!is_dir($projectRoot . $qrDir)) {
        mkdir($projectRoot . $qrDir, 0777, true);
    }

    $fileName = 'qr_img_' . time() . '.png';
    $fullSavePath = $projectRoot . $qrDir . $fileName;
    $dbPath = $qrDir . $fileName;

    $qrImageBase64 = $qrService->generate($qrContent, $fullSavePath, $options);

    if (!empty($qrImageBase64)) {
        $qrRepo->save(
                $type,
                $displayContent,
                $userId,
                $dbPath,
                $title
        );
    }

} catch (\Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Результат генерації - GenerQR</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>
<body>
<div class="container">
    <div class="card" style="text-align: center;">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="/QR-code generator/public/" class="apple-link" style="text-decoration: none;">← На головну</a>
        </div>
        <h1>Ваш QR-код</h1>

        <?php if (!empty($title)): ?>
            <h2 style="font-size: 18px; color: #86868b; margin-top: -10px; margin-bottom: 20px; font-weight: 500;">
                <?= htmlspecialchars($title) ?>
            </h2>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="color: #e74c3c; margin-bottom: 20px;">
                <strong>Помилка:</strong> <?= htmlspecialchars($error) ?>
            </div>
            <a href="/QR-code generator/public/" class="button-link" style="display: block; text-decoration: none;">
                <button type="button" style="background-color: #95a5a6;">Спробувати ще раз</button>
            </a>
        <?php else: ?>
            <div class="result-area">
                <img src="<?= $qrImageBase64 ?>" alt="QR Code" class="result-img" style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; background: white;">

                <div style="margin-bottom: 20px; word-break: break-all;">
                    <p>Вміст коду:</p>
                    <a href="<?= htmlspecialchars($displayContent) ?>" target="_blank" style="color: #0071e3; text-decoration: none;">
                        <?= htmlspecialchars($displayContent) ?>
                    </a>
                </div>

                <div class="actions">
                    <a href="<?= $qrImageBase64 ?>" download="qr-code.png" style="text-decoration: none;">
                        <button type="button" style="background-color: #27ae60; margin-bottom: 10px;">Скачати як PNG</button>
                    </a>

                    <button onclick="window.print()" style="margin-bottom: 10px;">Друкувати / PDF</button>

                    <a href="/QR-code generator/public/" style="text-decoration: none;">
                        <button type="button" style="background-color: #95a5a6;">Створити новий</button>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>