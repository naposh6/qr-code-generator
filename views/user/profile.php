<?php
/** @var array $user */
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Профіль користувача - GenerQR</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Мій профіль</h1>
            <a href="/QR-code generator/public/" style="text-decoration: none; color: #3498db;">← На головну</a>
        </div>

        <div class="profile-info" style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Роль в системі:</strong>
                <span class="badge" style="background: #2ecc71; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px;">
                    <?= strtoupper($user['role']) ?>
                </span>
            </p>
            <p><strong>Дата реєстрації:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
        </div>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Змінити пароль</h3>
        <form action="profile/update-password" method="POST">
            <div class="form-group">
                <label>Новий пароль</label>
                <input type="password" name="password" required minlength="6" placeholder="Мінімум 6 символів">
            </div>
            <div class="form-group">
                <label>Підтвердіть пароль</label>
                <input type="password" name="password_confirm" required>
            </div>
            <button type="submit" style="background-color: #34495e;">Оновити пароль</button>
        </form>

        <?php if (isset($_GET['success'])): ?>
            <p style="color: #27ae60; margin-top: 15px;">✔ Дані успішно оновлено!</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>