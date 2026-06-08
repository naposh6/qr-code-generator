<?php
/** @var string $baseDir */
$appUrl = rtrim(ASSETS_URL, '/');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація - GenerQR</title>
    <link rel="icon" type="image/png" href="<?= $appUrl ?>/assets/logo-qr.png">
    <link rel="stylesheet" href="<?= $appUrl ?>/css/style.css">
</head>
<script src="<?= $appUrl ?>/js/theme.js" defer></script>
<body>
<div class="container">
    <div class="card">
        <h1>Реєстрація</h1>
        <?php if (!empty($error)): ?>
            <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:12px;border-radius:8px;margin-bottom:16px;color:#856404;font-size:14px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Зареєструватися</button>
        </form>
        <p style="text-align:center;margin-top:16px;">Вже є акаунт? <a href="<?= BASE_DIR ?>/login">Увійти</a></p>
    </div>
</div>
<footer class="site-footer-bar" style="margin-top: 24px;">
    © 2026 QR Code Generator · Powered by <a href="#" tabindex="-1">naposh</a>
</footer>
</body>
</html>
