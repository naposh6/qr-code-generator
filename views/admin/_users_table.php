<?php
/** @var array $allUsers */
?>
<h3 style="margin-top: 0; margin-bottom: 20px;">Користувачі системи</h3>
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
                    <select name="role" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 13px; width: auto; background: none; border: 1px solid #d2d2d7; border-radius: 8px;">
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </form>
            </td>
            <td style="padding: 15px 0; text-align: right;">
                <?php if ($user['role'] !== 'admin'): ?>
                    <a href="admin/delete-user?id=<?= $user['id'] ?>" style="color: #ff3b30; text-decoration: none; font-size: 14px;" onclick="return confirm('Видалити?')">Видалити</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>