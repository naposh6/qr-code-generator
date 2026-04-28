<?php /** @var array $allQrs */ ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h3 style="margin: 0;">Всі генерації системи</h3>
    <button id="delete-selected" class="apple-link" style="display: none; color: #ff3b30; background: rgba(255, 59, 48, 0.1); border: none; padding: 8px 15px; border-radius: 12px; cursor: pointer; font-weight: 600;">
        🗑 Видалити обрані (<span id="selected-count">0</span>)
    </button>
</div>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
    <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 13px;">
        <th style="padding: 15px 0; width: 40px;">
            <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
        </th>
        <th style="padding: 15px 0;">ТИП</th>
        <th style="padding: 15px 0;">КОНТЕНТ</th>
        <th style="padding: 15px 0;">ДАТА</th>
        <th style="padding: 15px 0; text-align: right;">ДІЇ</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($allQrs)): ?>
        <tr><td colspan="5" style="padding: 20px; text-align: center;">QR-кодів ще немає</td></tr>
    <?php else: ?>
        <?php foreach ($allQrs as $qr): ?>
            <tr style="border-bottom: 1px solid #f5f5f7;" data-id="<?= $qr['id'] ?>">
                <td style="padding: 15px 0;">
                    <input type="checkbox" class="qr-checkbox" value="<?= $qr['id'] ?>" style="width: 18px; height: 18px; cursor: pointer;">
                </td>
                <td style="padding: 15px 0;"><span class="badge"><?= strtoupper($qr['qr_type']) ?></span></td>
                <td style="padding: 15px 0; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= htmlspecialchars($qr['original_url']) ?>
                </td>
                <td style="padding: 15px 0; color: #86868b; font-size: 13px;">
                    <?= date('d.m.Y', strtotime($qr['created_at'])) ?>
                </td>
                <td style="padding: 15px 0; text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                    <button class="apple-link view-qr-btn"
                            style="background: none; border: none; cursor: pointer; color: #0071e3;"
                            data-path="<?= htmlspecialchars($qr['media_path'] ?? '') ?>"
                            data-content="<?= htmlspecialchars($qr['original_url'] ?? '') ?>">
                        Переглянути
                    </button>
                    <button class="delete-qr-btn" data-id="<?= $qr['id'] ?>"
                            style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 20px; padding: 0 5px;"
                            title="Видалити">&times;</button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>