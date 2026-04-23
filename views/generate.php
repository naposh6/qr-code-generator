<?php

use App\Factories\QrContentFactory;
use App\Services\QrGeneratorService;
use App\Services\FileService;

$qrService = new QrGeneratorService();
$fileService = new FileService();

$type = $_POST['type'] ?? 'url';
$error = null;
$qrImageBase64 = '';
$displayContent = '';

try {
    $data = '';

    if ($type === 'image' || $type === 'video') {
        try {
            $relativePath = $fileService->upload($_FILES['qr_file']);
            $contentString = "http://localhost/QR-code generator/public/" . $relativePath;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $data = $_POST['content'] ?? '';
        if (empty(trim($data))) {
            throw new Exception("Контент не може бути порожнім.");
        }
    }

    $qrContent = QrContentFactory::create($type, $data);

    $displayContent = $qrContent->getContent();

    $qrImageBase64 = $qrService->generate($qrContent);

    if (!$error && !empty($qrImageBase64)) {
        try {
            $qrRepo = new \App\Repositories\QrRepository();

            $qrRepo->save(
                $type,
                $displayContent,
                $relativePath ?? null
            );
        } catch (\Exception $dbEx) {
            error_log("Database Error: " . $dbEx->getMessage());
        }
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