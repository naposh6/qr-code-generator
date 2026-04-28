<?php

use App\Repositories\QrRepository;

$qrRepo = new QrRepository();
$history = $qrRepo->getAll(5);

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'guest';

if ($userRole === 'admin') {
    $history = $qrRepo->getAll(10);
} else {
    $history = $qrRepo->getByUserId($userId, 5);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>GenerQR</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <nav class="user-nav">
                <div class="user-info">
                    <span>Привіт, <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong></span>
                </div>
                <div class="nav-links">
                    <a href="profile" class="apple-link">Мій профіль</a>

                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="admin" class="admin-badge">⚙️ Адмін-панель</a>
                    <?php endif; ?>

                    <a href="logout" class="apple-link logout-link">Вихід</a>
                </div>
            </nav>
            <h1>GenerQR</h1>
            <form action="/QR-code generator/public/generate" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Тип контенту</label>
                    <select name="type" id="typeSelect" onchange="toggleInputs()">
                        <option value="url">Посилання</option>
                        <option value="text">Текст</option>
                        <option value="image">Фото</option>
                        <option value="video">Відео</option>
                    </select>
                </div>

                <div id="textContentDiv" class="form-group">
                    <label>Введіть дані</label>
                    <input type="text" name="content" placeholder="https://...">
                </div>

                <div id="fileContentDiv" class="form-group" style="display: none;">
                    <label>Оберіть файл</label>
                    <input type="file" name="qr_file">
                </div>

                <button type="submit">Згенерувати QR</button>
            </form>
            <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

            <h3>Останні генерації</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px;">
                    <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left;">Тип</th>
                        <th style="padding: 10px; text-align: left;">Контент</th>
                        <th style="padding: 10px; text-align: left;">Дата</th>
                        <th style="padding: 10px; text-align: right;">Дія</th> </tr>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="3" style="padding: 15px; color: #777;">Історія поки порожня</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;">
            <span class="badge" style="background: #3498db; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;">
                <?= htmlspecialchars($item['qr_type']) ?>
            </span>
                                </td>
                                <td style="padding: 10px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($item['original_url']) ?>
                                </td>
                                <td style="padding: 10px; color: #999; font-size: 12px;">
                                    <?= date('d.m H:i', strtotime($item['created_at'])) ?>
                                </td>

                                <td style="padding: 10px; text-align: right;">
                                    <a href="delete.php?id=<?= $item['id'] ?>"
                                       onclick="return confirm('Видалити цей QR-код з історії?')"
                                       style="color: #e74c3c; text-decoration: none; font-weight: bold; font-size: 18px; padding: 0 5px;"
                                       title="Видалити">
                                        &times;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
    function toggleInputs() {
        const type = document.getElementById('typeSelect').value;
        const isFile = (type === 'image' || type === 'video');
        document.getElementById('textContentDiv').style.display = isFile ? 'none' : 'block';
        document.getElementById('fileContentDiv').style.display = isFile ? 'block' : 'none';
    }
</script>
</body>
</html>
