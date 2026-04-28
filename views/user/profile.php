<?php
/** @var array $user */
/** @var array $userQrs */
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
            <a href="/QR-code generator/public/" class="apple-link secondary">← На головну</a>
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

        <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 20px;">
            <div class="stat-card">
                <p>Всього створено</p>
                <h2><?= count($userQrs) ?></h2>
            </div>
            <div class="stat-card">
                <p>Остання активність</p>
                <h2 style="font-size: 18px;"><?= $userQrs[0]['created_at'] ?? 'Немає даних' ?></h2>
            </div>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Мої QR-коди</h3>

                <button id="delete-selected"
                        style="display: none; align-items: center; gap: 6px; color: #ff3b30; background: #fff5f5; border: 1px solid rgba(255, 59, 48, 0.2); padding: 8px 16px; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: 600; width: auto;">
                    🗑 Видалити обрані (<span id="selected-count">0</span>)
                </button>
            </div>

            <?php if (empty($userQrs)): ?>
                <p style="text-align: center; color: #86868b; padding: 20px;">У вас ще немає згенерованих QR-кодів</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                    <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 13px;">
                        <th style="padding: 15px 0; width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
                        </th>
                        <th style="padding: 15px 0; width: 80px;">ТИП</th>
                        <th style="padding: 15px 0;">ВМІСТ</th>
                        <th style="padding: 15px 0; text-align: right; width: 150px;">ДІЇ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($userQrs as $qr): ?>
                        <tr style="border-bottom: 1px solid #f5f5f7;">
                            <td style="padding: 15px 0; text-align: center;">
                                <input type="checkbox" class="qr-checkbox" value="<?= $qr['id'] ?>" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
                            <td style="padding: 15px 0;">
                                <span class="badge" style="background: #e8e8ed; color: #1d1d1f;"><?= strtoupper($qr['qr_type']) ?></span>
                            </td>
                            <td style="padding: 15px 0; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($qr['original_url']) ?>
                            </td>
                            <td style="padding: 15px 0; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                                    <button class="view-qr-btn"
                                            style="background: #f5f5f7; border: none; padding: 8px 15px; border-radius: 20px; color: #0071e3; cursor: pointer; font-weight: 500; font-size: 13px; width: auto;"
                                            data-path="<?= htmlspecialchars($qr['media_path'] ?? '') ?>"
                                            data-content="<?= htmlspecialchars($qr['original_url']) ?>">
                                        Переглянути
                                    </button>
                                    <button class="delete-qr-btn" data-id="<?= $qr['id'] ?>"
                                            style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 24px; line-height: 1; width: auto;"
                                            title="Видалити">&times;</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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

<div id="qrModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); transition: all 0.3s ease;">
    <div class="card" style="position:relative; margin: 8% auto; max-width: 400px; text-align:center; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <span id="closeModal" style="position:absolute; right:20px; top:10px; cursor:pointer; font-size:28px; color: #86868b;">&times;</span>
        <h3 id="modalTitle" style="margin-bottom: 20px;">Ваш QR-код</h3>
        <img id="modalImg" src="" style="max-width:100%; border-radius:12px; border: 1px solid #d2d2d7;">
        <p id="modalContent" style="word-break: break-all; margin-top: 20px; color: #0071e3; font-size: 14px;"></p>
        <a id="modalDownload" href="" download="qr-code.png" class="apple-link" style="display: block; margin-top: 20px; background: #0071e3; color: white; padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 600;">
            Завантажити PNG
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('qrModal');
        const modalImg = document.getElementById('modalImg');
        const modalContent = document.getElementById('modalContent');
        const modalDownload = document.getElementById('modalDownload');
        const baseAppPath = '/QR-code generator/public/';

        const deleteBtnSelected = document.getElementById('delete-selected');
        const countSpan = document.getElementById('selected-count');
        const selectAll = document.getElementById('selectAll');

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('view-qr-btn')) {
                const rawPath = e.target.getAttribute('data-path');
                const content = e.target.getAttribute('data-content');

                if (!rawPath) { alert('Зображення відсутнє'); return; }

                const fullPath = baseAppPath + rawPath;
                modalImg.src = fullPath;
                modalContent.innerText = content;
                modalDownload.href = fullPath;
                modalDownload.setAttribute('download', 'qr-code.png');
                modal.style.display = 'flex';
            }
        });

        if (document.getElementById('closeModal')) {
            document.getElementById('closeModal').onclick = () => modal.style.display = 'none';
        }
        window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; }

        function updateBulkButton() {
            const selected = document.querySelectorAll('.qr-checkbox:checked').length;
            if (deleteBtnSelected) {
                deleteBtnSelected.style.display = selected > 0 ? 'block' : 'none';
                countSpan.innerText = selected;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.qr-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkButton();
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('qr-checkbox')) {
                updateBulkButton();
            }
        });

        function sendDeleteRequest(ids) {
            if (!confirm(`Видалити обрані QR-коди (${ids.length} шт.)?`)) return;

            const isAdminPage = window.location.pathname.includes('/admin');
            const deleteUrl = isAdminPage ? 'admin/delete-qrs' : 'delete-qrs';

            fetch(baseAppPath + deleteUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Помилка: ' + (data.message || 'Не вдалося видалити'));
                    }
                })
                .catch(err => console.error('Помилка запиту:', err));
        }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('delete-qr-btn')) {
                const id = e.target.getAttribute('data-id');
                sendDeleteRequest([id]);
            }
            if (e.target && e.target.id === 'delete-selected') {
                const ids = Array.from(document.querySelectorAll('.qr-checkbox:checked')).map(cb => cb.value);
                sendDeleteRequest(ids);
            }
        });
    });
</script>
</body>
</html>