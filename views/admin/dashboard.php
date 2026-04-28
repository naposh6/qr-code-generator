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
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <h1 style="margin: 0; font-weight: 700; letter-spacing: -0.5px;">Керування системою</h1>
        <a href="/QR-code generator/public/" class="apple-link">← Назад</a>
    </header>

    <div class="stats-grid">
        <div class="card stat-card">
            <span style="color: #86868b; font-size: 14px; font-weight: 600;">КОРИСТУВАЧІ</span>
            <h2 style="font-size: 32px; margin: 10px 0;"><?= $stats['total_users'] ?></h2>
        </div>
        <div class="card stat-card">
            <span style="color: #86868b; font-size: 14px; font-weight: 600;">QR-КОДИ</span>
            <h2 style="font-size: 32px; margin: 10px 0;"><?= $stats['total_qrs'] ?></h2>
        </div>
    </div>

    <div class="card" style="margin-top: 30px; overflow-x: auto;">
        <h3 style="margin-top: 0;">Користувачі</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 13px;">
                <th style="padding: 15px 0;">EMAIL</th>
                <th style="padding: 15px 0;">РОЛЬ</th>
                <th style="padding: 15px 0; text-align: right;">ДІЇ</th>
            </tr>
            <?php foreach ($allUsers as $user): ?>
                <tr style="border-bottom: 1px solid #f5f5f7;">
                    <td style="padding: 15px 0; font-weight: 500;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 15px 0;">
                        <form action="admin/update-role" method="POST" style="margin: 0;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 13px; width: auto; background: none;">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </form>
                    </td>
                    <td style="padding: 15px 0; text-align: right;">
                        <?php if ($user['role'] !== 'admin'): ?>
                            <a href="admin/delete-user?id=<?= $user['id'] ?>" style="color: #ff3b30; text-decoration: none; font-size: 14px;">Видалити</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>