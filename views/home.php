<?php

use App\Repositories\QrRepository;

if (!isset($recentQrs)) {
    $qrRepo = new QrRepository();
    $userId = $_SESSION['user_id'] ?? null;
    $recentQrs = $userId ? $qrRepo->getByUserId($userId, 5) : [];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>GenerQR — Головна</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <nav class="user-nav">
            <div class="user-info">
                <span>Привіт, <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong></span>
            </div>
            <div class="nav-links">
                <a href="profile" class="apple-link">Мій профіль</a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="admin" class="admin-badge">⚙️ Адмін-панель</a>
                <?php endif; ?>
                <a href="logout" class="apple-link logout-link">Вихід</a>
            </div>
        </nav>

        <h1>GenerQR</h1>
        <form action="/QR-code generator/public/generate" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Тип контенту</label>
                <select name="type" id="typeSelect" onchange="toggleInputs()">
                    <option value="url">Посилання</option>
                    <option value="text">Текст</option>
                    <option value="image">Фото</option>
                    <option value="video">Відео</option>
                </select>
            </div>

            <div id="textContentDiv" class="form-group">
                <label>Введіть дані</label>
                <input type="text" name="content" placeholder="https://...">
            </div>

            <div id="fileContentDiv" class="form-group" style="display: none;">
                <label>Оберіть файл</label>
                <input type="file" name="qr_file">
            </div>

            <button type="submit">Згенерувати QR</button>
        </form>
    </div>

    <?php if (!empty($recentQrs)): ?>
        <div class="card" style="margin-top: 40px; border-top: 1px solid #f5f5f7;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Ваші останні генерації</h3>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <button id="delete-selected"
                            style="display: none; align-items: center; gap: 6px; color: #ff3b30; background: #fff5f5; border: 1px solid rgba(255, 59, 48, 0.2); padding: 6px 12px; border-radius: 12px; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap; width: auto;">
                        🗑 Видалити (<span id="selected-count">0</span>)
                    </button>

                    <a href="/QR-code generator/public/profile"
                       class="apple-link"
                       style="font-size: 13px; font-weight: 500; color: #0071e3; text-decoration: none; white-space: nowrap;">
                        Вся історія →
                    </a>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="border-bottom: 1px solid #f5f5f7; text-align: left;">
                    <th style="padding: 14px 0; width: 40px; text-align: center;">
                        <input type="checkbox" id="selectAll" style="width: 16px; height: 16px; cursor: pointer;">
                    </th>
                    <th style="padding: 14px 0; font-size: 12px; color: #86868b; width: 80px;">ТИП</th>
                    <th style="padding: 14px 0; font-size: 12px; color: #86868b;">ВМІСТ</th>
                    <th style="padding: 14px 0; text-align: right; font-size: 12px; color: #86868b; width: 150px;">ДІЇ</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentQrs as $qr): ?>
                    <tr style="border-bottom: 1px solid #f5f5f7;">
                        <td style="padding: 14px 0; text-align: center;">
                            <input type="checkbox" class="qr-checkbox" value="<?= $qr['id'] ?>" style="width: 16px; height: 16px; cursor: pointer;">
                        </td>
                        <td style="padding: 14px 0;">
                            <span class="badge" style="font-size: 10px; padding: 4px 8px;"><?= strtoupper($qr['qr_type']) ?></span>
                        </td>
                        <td style="padding: 14px 0; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 14px; color: #424245;">
                            <?= htmlspecialchars($qr['original_url']) ?>
                        </td>
                        <td style="padding: 14px 0; text-align: right;">
                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                                <button class="view-qr-btn"
                                        style="font-size: 12px; background: #0071e3; color: white; border: none; cursor: pointer; padding: 6px 12px; border-radius: 15px; font-weight: 500; width: auto;"
                                        data-path="<?= htmlspecialchars($qr['media_path'] ?? '') ?>"
                                        data-content="<?= htmlspecialchars($qr['original_url']) ?>">
                                    Переглянути
                                </button>
                                <button class="delete-qr-btn" data-id="<?= $qr['id'] ?>"
                                        style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 22px; line-height: 1; padding: 0 5px; width: auto;"
                                        title="Видалити">&times;</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div id="qrModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);">
    <div class="card" style="position:relative; margin: 8% auto; max-width: 400px; text-align:center; padding: 40px;">
        <span id="closeModal" style="position:absolute; right:20px; top:10px; cursor:pointer; font-size:28px; color: #86868b;">&times;</span>
        <h3 style="margin-bottom: 20px;">QR-код</h3>
        <img id="modalImg" src="" style="max-width:100%; border-radius:12px; border: 1px solid #d2d2d7;">
        <p id="modalContent" style="word-break: break-all; margin-top: 20px; color: #0071e3; font-size: 14px;"></p>
        <a id="modalDownload" href="" download="qr-code.png" class="apple-link" style="display: block; margin-top: 20px; background: #1d1d1f; color: white; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 500;">Завантажити PNG</a>
    </div>
</div>

<script>
    function toggleInputs() {
        const type = document.getElementById('typeSelect').value;
        const isFile = (type === 'image' || type === 'video');
        document.getElementById('textContentDiv').style.display = isFile ? 'none' : 'block';
        document.getElementById('fileContentDiv').style.display = isFile ? 'block' : 'none';
    }

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
