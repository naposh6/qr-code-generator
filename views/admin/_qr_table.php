<?php /** @var array $allQrs */ ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 5px;">
    <h3 style="margin: 0; font-weight: 600;">Всі генерації системи</h3>
    <button id="delete-selected" class="apple-link" style="display: none; color: #ff3b30; background: rgba(255, 59, 48, 0.1); border: none; padding: 8px 15px; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
        🗑 Видалити обрані (<span id="selected-count">0</span>)
    </button>
</div>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
    <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
        <th style="padding: 15px 10px; width: 40px; text-align: center;">
            <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
        </th>
        <th style="padding: 15px 10px;">Тип</th>
        <th style="padding: 15px 10px;">Вміст та QR</th>
        <th style="padding: 15px 10px;">Дата створення</th>
        <th style="padding: 15px 10px; text-align: right;">Дії</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($allQrs)): ?>
        <tr><td colspan="5" style="padding: 40px; text-align: center; color: #86868b;">В системі ще не створено жодного QR-коду</td></tr>
    <?php else: ?>
        <?php foreach ($allQrs as $qr): ?>
            <tr style="border-bottom: 1px solid #f5f5f7; transition: background 0.2s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                <td style="padding: 12px 10px; text-align: center;">
                    <input type="checkbox" class="qr-checkbox" value="<?= $qr['id'] ?>" style="width: 18px; height: 18px; cursor: pointer;">
                </td>

                <td style="padding: 12px 10px;">
                    <span class="badge" style="background: #e8e8ed; color: #1d1d1f; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
                        <?= strtoupper($qr['qr_type']) ?>
                    </span>
                </td>

                <td style="padding: 12px 10px; max-width: 350px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="position: relative; width: 44px; height: 44px; flex-shrink: 0; background: #fff; padding: 2px; border-radius: 8px; border: 1px solid #d2d2d7; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                            <img src="/QR-code generator/public/<?= htmlspecialchars($qr['media_path']) ?>"
                                 style="width: 100%; height: 100%; border-radius: 4px; object-fit: contain;"
                                 alt="Mini QR">

                            <div style="position: absolute; bottom: -4px; right: -4px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 1px solid #d2d2d7; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <?php if ($qr['qr_type'] === 'image'): ?>🖼️
                                <?php elseif ($qr['qr_type'] === 'video'): ?>🎥
                                <?php else: ?>🔗
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="overflow: hidden;">
                            <div style="font-weight: 600; font-size: 13px; color: #1d1d1f; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars(basename($qr['original_url'])) ?>
                            </div>
                            <div style="font-size: 11px; color: #0071e3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px;">
                                <?= htmlspecialchars($qr['original_url']) ?>
                            </div>
                        </div>
                    </div>
                </td>

                <td style="padding: 12px 10px; color: #86868b; font-size: 13px;">
                    <?= date('d.m.Y', strtotime($qr['created_at'])) ?>
                    <div style="font-size: 10px; opacity: 0.7;"><?= date('H:i', strtotime($qr['created_at'])) ?></div>
                </td>

                <td style="padding: 12px 10px; text-align: right;">
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
                        <button class="apple-link view-qr-btn"
                                style="background: #f5f5f7; border: none; padding: 6px 12px; border-radius: 20px; color: #0071e3; cursor: pointer; font-weight: 500; font-size: 12px; transition: all 0.2s;"
                                data-path="<?= htmlspecialchars($qr['media_path'] ?? '') ?>"
                                data-content="<?= htmlspecialchars($qr['original_url'] ?? '') ?>">
                            Переглянути
                        </button>
                        <button class="delete-qr-btn" data-id="<?= $qr['id'] ?>"
                                style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 24px; line-height: 1; padding: 0 5px; opacity: 0.6; transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'"
                                title="Видалити">&times;</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>