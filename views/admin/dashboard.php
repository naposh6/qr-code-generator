<?php
/** @var array $allUsers */
/** @var array $allQrs */
/** @var array $stats */
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін-панель</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h2 { font-size: 24px; color: #3498db; }
    </style>
</head>
<body>
<div class="container">
    <h1>Панель керування (Адмін)</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <p>Користувачів</p>
            <h2><?= $stats['total_users'] ?></h2>
        </div>
        <div class="stat-card">
            <p>Всього QR-кодів</p>
            <h2><?= $stats['total_qrs'] ?></h2>
        </div>
        <div class="stat-card">
            <p>Останній юзер</p>
            <small><?= $stats['latest_user'] ?></small>
        </div>
    </div>

    <h3>Керування користувачами</h3>
    <table style="width:100%; background: #fff; border-radius: 8px; margin-bottom: 40px; border-collapse: collapse;">
        <thead>
        <tr style="background: #f1f1f1;">
            <th style="padding: 10px;">Email</th>
            <th style="padding: 10px;">Роль</th>
            <th style="padding: 10px;">Дата реєстрації</th>
            <th style="padding: 10px; text-align: right;">Дія</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allUsers as $user): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;"><?= htmlspecialchars($user['email']) ?></td>
                <td style="padding: 10px;"><?= $user['role'] ?></td>
                <td style="padding: 10px;"><?= $user['created_at'] ?></td>
                <td style="padding: 10px; text-align: right;">
                    <?php if ($user['role'] !== 'admin'): ?>
                        <a href="admin/delete-user?id=<?= $user['id'] ?>"
                           style="color: white; background: #e74c3c; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 12px;"
                           onclick="return confirm('Видалити цього користувача?')">Видалити</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Всі генерації системи</h3>
    <table style="width:100%; background: #fff; border-radius: 8px;">
        <thead>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Тип</th>
            <th>Контент</th>
            <th>Дата</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allQrs as $qr): ?>
            <tr>
                <td><?= $qr['id'] ?></td>
                <td><strong>User #<?= $qr['user_id'] ?></strong></td>
                <td><?= $qr['qr_type'] ?></td>
                <td><?= htmlspecialchars($qr['original_url']) ?></td>
                <td><?= $qr['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <a href="./" style="color: #3498db;">← Повернутися до свого кабінету</a>
</div>
</body>
</html>