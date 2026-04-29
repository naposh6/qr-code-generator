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

            <div class="form-group">
                <label>Назва (необов'язково)</label>
                <input type="text" name="title" placeholder="Наприклад: Меню ресторану">
            </div>

            <div class="editor-settings" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; padding: 15px; background: #f5f5f7; border-radius: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 12px;">Колір коду</label>
                    <input type="color" name="qr_color" value="#000000" style="height: 40px; padding: 2px; cursor: pointer; border: none; background: none; width: 100%;">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 12px;">Колір фону</label>
                    <input type="color" name="bg_color" value="#ffffff" style="width: 100%;">
                </div>
                <div class="form-group" style="margin: 0; grid-column: span 2;">
                    <label style="font-size: 12px;">Логотип (PNG/JPG)</label>
                    <input type="file" name="qr_logo" accept="image/*" style="font-size: 11px;">
                </div>
                <div class="form-group" style="margin: 0; grid-column: span 2;">
                    <label style="font-size: 12px;">Стиль точок</label>
                    <select name="qr_style" style="width: 100%;">
                        <option value="square">Класичні квадрати</option>
                        <option value="circle">Круглі модулі (Dots)</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 12px;">Розмір (px)</label>
                    <select name="qr_size" style="width: 100%;">
                        <option value="200">Маленький (200)</option>
                        <option value="400" selected>Середній (400)</option>
                        <option value="600">Великий (600)</option>
                    </select>
                </div>
            </div>
            <button type="submit" style="margin-top: 20px;">Згенерувати QR</button>
        </form>
    </div>

    <?php if (!empty($recentQrs)): ?>
        <div class="card" style="margin-top: 40px; border-top: 1px solid #f5f5f7;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 5px;">
                <h3 style="margin: 0; font-weight: 600;">Ваші останні генерації</h3>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <button id="delete-selected" class="apple-link" style="display: none; color: #ff3b30; background: rgba(255, 59, 48, 0.1); border: none; padding: 8px 15px; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                        🗑 Видалити (<span id="selected-count">0</span>)
                    </button>

                    <a href="/QR-code generator/public/profile" class="apple-link" style="font-size: 13px; font-weight: 500; color: #0071e3; text-decoration: none;">
                        Вся історія →
                    </a>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 15px 10px; width: 40px; text-align: center;">
                        <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
                    </th>
                    <th style="padding: 15px 10px; width: 80px;">Тип</th>
                    <th style="padding: 15px 10px;">Вміст та QR</th>
                    <th style="padding: 15px 10px; width: 140px;">Дата</th>
                    <th style="padding: 15px 10px; text-align: right; width: 120px;">Дії</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentQrs as $qr): ?>
                    <tr style="border-bottom: 1px solid #f5f5f7; transition: background 0.2s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px 10px; text-align: center;">
                            <input type="checkbox" class="qr-checkbox" value="<?= $qr['id'] ?>" style="width: 18px; height: 18px; cursor: pointer;">
                        </td>
                        <td style="padding: 12px 10px;">
                    <span class="badge" style="background: #e8e8ed; color: #1d1d1f; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
                        <?= strtoupper($qr['qr_type']) ?>
                    </span>
                        </td>

                        <td style="padding: 12px 10px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="position: relative; width: 44px; height: 44px; flex-shrink: 0; background: #fff; padding: 2px; border-radius: 8px; border: 1px solid #d2d2d7; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                    <img src="/QR-code generator/public/<?= htmlspecialchars($qr['media_path']) ?>"
                                         style="width: 100%; height: 100%; border-radius: 4px; object-fit: contain;"
                                         alt="QR Mini">

                                    <div style="position: absolute; bottom: -4px; right: -4px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 1px solid #d2d2d7; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <?php if ($qr['qr_type'] === 'image'): ?>🖼️
                                        <?php elseif ($qr['qr_type'] === 'video'): ?>🎥
                                        <?php else: ?>🔗
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div style="overflow: hidden; max-width: 250px;">
                                    <div style="font-weight: 600; font-size: 13px; color: #1d1d1f; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= !empty($qr['title']) ? htmlspecialchars($qr['title']) : htmlspecialchars(basename($qr['original_url'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #0071e3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars($qr['original_url']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td style="padding: 12px 10px; color: #86868b; font-size: 13px; white-space: nowrap;">
                            <?= isset($qr['created_at']) ? date('d.m.Y', strtotime($qr['created_at'])) : '—' ?>
                            <div style="font-size: 10px; opacity: 0.7; margin-top: 2px;"><?= isset($qr['created_at']) ? date('H:i', strtotime($qr['created_at'])) : '' ?></div>
                        </td>

                        <td style="padding: 12px 10px; text-align: right;">
                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
                                <button class="apple-link view-qr-btn"
                                        style="background: #f5f5f7; border: none; padding: 6px 12px; border-radius: 20px; color: #0071e3; cursor: pointer; font-weight: 500; font-size: 12px; transition: all 0.2s;"
                                        data-path="<?= htmlspecialchars($qr['media_path'] ?? '') ?>"
                                        data-content="<?= htmlspecialchars($qr['original_url']) ?>">
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

    document.querySelector('form').addEventListener('submit', function(e) {
        const fileInput = document.querySelector('input[name="qr_file"]');
        const type = document.getElementById('typeSelect').value;

        if ((type === 'image' || type === 'video') && fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size / 1024 / 1024; // в МБ
            const limit = 20;

            if (fileSize > limit) {
                e.preventDefault();
                alert(`Файл занадто великий (${fileSize.toFixed(2)} МБ). Максимум: ${limit} МБ.`);
            }
        }
    });
</script>
</body>
</html>
