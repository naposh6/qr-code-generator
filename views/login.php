<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід - GenerQR</title>
    <link rel="icon" type="image/png" href="/QR-code generator/public/assets/logo-qr.png">
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>

<script src="/QR-code generator/public/js/theme.js" defer></script>
<body>
<div class="container">
    <div class="card">
        <h1>Вхід</h1>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Увійти</button>
        </form>
        <p>Немає акаунту? <a href="register">Реєстрація</a></p>
    </div>
</div>
<footer class="site-footer-bar" style="margin-top: 24px;">
    © 2026 QR Code Generator · Powered by <a href="#" tabindex="-1">naposh</a>
</footer>
</div>
</body>
</html>