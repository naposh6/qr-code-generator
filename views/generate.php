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

$error = null;
$qrImageBase64 = '';
$displayContent = '';
$relativePath = null;

$generatedFilePath = "uploads/qr/qr_" . time() . ".png";

$qrRepo = new QrRepository();
$success = $qrRepo->save($type, $content, $userId, $generatedFilePath);

try {
    $finalData = '';

    if ($type === 'image' || $type === 'video') {
        if (!isset($_FILES['qr_file']) || $_FILES['qr_file']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Будь ласка, виберіть файл.");
        }

        $relativePath = $fileService->upload($_FILES['qr_file']);

        $finalData = "http://localhost/QR-code generator/public/" . $relativePath;
    } else {
        $finalData = $_POST['content'] ?? '';
        if (empty(trim($finalData))) {
            throw new Exception("Контент не може бути порожнім.");
        }
    }

    $qrContent = QrContentFactory::create($type, $finalData);

    $displayContent = $qrContent->getContent();

    $qrImageBase64 = $qrService->generate($qrContent);

    if (!empty($qrImageBase64)) {
        $qrRepo->save(
                $type,
                $displayContent,
                $userId,
                $relativePath
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
        <h1>Ваш QR-код</h1>

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
                    <label>Вміст коду:</label>
                    <p><code><?= htmlspecialchars($displayContent) ?></code></p>
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