<?php /** @var array $allUsers */ ?>
<h3 style="margin-top:0;margin-bottom:20px;color:var(--text);">Користувачі системи</h3>
<table style="width:100%;border-collapse:collapse;">
    <tr style="border-bottom:1px solid var(--border-solid);text-align:left;color:var(--text-2);font-size:13px;">
        <th style="padding:15px 0;">EMAIL</th>
        <th style="padding:15px 0;">РОЛЬ</th>
        <th style="padding:15px 0;text-align:right;">ДІЇ</th>
    </tr>
    <?php foreach ($allUsers as $user): ?>
        <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:15px 0;font-weight:500;color:var(--text);"><?= htmlspecialchars($user['email']) ?></td>
            <td style="padding:15px 0;">
                <form action="<?= BASE_DIR ?>/admin/update-role" method="POST" style="margin:0;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="role" onchange="this.form.submit()" style="padding:4px 8px;font-size:13px;width:auto;background:var(--input-bg);border:1px solid var(--input-border);border-radius:8px;color:var(--text);">
                        <option value="user"  <?= $user['role'] === 'user'  ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </form>
            </td>
            <td style="padding:15px 0;text-align:right;">
                <?php if ($user['role'] !== 'admin'): ?>
                    <a href="<?= BASE_DIR ?>/admin/delete-user?id=<?= $user['id'] ?>"
                       style="color:var(--danger);text-decoration:none;font-size:14px;"
                       onclick="return confirm('Видалити?')">Видалити</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
