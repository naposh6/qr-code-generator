<?php /** @var array $allQrs */ ?>
<h3 style="margin-top: 0; margin-bottom: 20px;">Всі генерації системи</h3>
<table style="width: 100%; border-collapse: collapse;">
    <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 13px;">
        <th style="padding: 15px 0;">ID</th>
        <th style="padding: 15px 0;">ТИП</th>
        <th style="padding: 15px 0;">КОНТЕНТ</th>
        <th style="padding: 15px 0; text-align: right;">ДАТА</th>
    </tr>
    <?php if (empty($allQrs)): ?>
        <tr><td colspan="4" style="padding: 20px; text-align: center;">QR-кодів ще немає</td></tr>
    <?php else: ?>
        <?php foreach ($allQrs as $qr): ?>
            <tr style="border-bottom: 1px solid #f5f5f7;">
                <td style="padding: 15px 0;"><?= $qr['id'] ?></td>
                <td style="padding: 15px 0;"><span class="badge"><?= strtoupper($qr['qr_type']) ?></span></td>
                <td style="padding: 15px 0; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= htmlspecialchars($qr['original_url']) ?>
                </td>
                <td style="padding: 15px 0; text-align: right; color: #86868b; font-size: 13px;">
                    <?= date('d.m.Y', strtotime($qr['created_at'])) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>