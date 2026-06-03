<?php
/** @var array $user */
/** @var array $userQrs */
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Профіль користувача - GenerQR</title>
    <link rel="icon" type="image/png" href="/QR-code generator/public/assets/logo-qr.png">
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>

<script src="/QR-code generator/public/js/theme.js" defer></script>
<body>
<div class="container">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Мій профіль</h1>
            <a href="/QR-code generator/public/" class="apple-link secondary">← На головну</a>
        </div>

        <div class="profile-info" style="margin-top: 20px; padding: 25px; background: var(--surface-2); border-radius: 18px; display: flex; align-items: flex-start; gap: 30px; border: 1px solid var(--border);">
            <div style="text-align: center; flex-shrink: 0;">
                <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid var(--border-solid); box-shadow: var(--shadow-sm); background: var(--badge-bg); margin-bottom: 10px;">
                    <img src="<?= !empty($user['avatar_path']) ? '/QR-code generator/public/' . $user['avatar_path'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['email']) . '&background=random' ?>"
                         style="width: 100%; height: 100%; object-fit: cover;" alt="Avatar">
                </div>
                <form action="profile/update-avatar" method="POST" enctype="multipart/form-data" id="avatarForm">
                    <label for="avatarInput" style="font-size: 12px; color: var(--accent); cursor: pointer; font-weight: 600;">Змінити фото</label>
                    <input type="file" name="avatar" id="avatarInput" style="display: none;" onchange="document.getElementById('avatarForm').submit()">
                </form>
            </div>

            <div style="flex: 1;">
                <div style="margin-bottom: 16px;">
                    <p style="margin: 4px 0; font-size: 13px; color: var(--text-2);"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p style="margin: 4px 0; font-size: 13px; color: var(--text-2);"><strong>Роль:</strong>
                        <span class="badge" style="background: #2ecc71; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px;"><?= strtoupper($user['role']) ?></span>
                    </p>
                    <p style="margin: 4px 0; font-size: 13px; color: var(--text-3);">В системі з: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                </div>

                <form action="profile/update-nickname" method="POST" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin: 0; flex: 1; min-width: 160px;">
                        <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-3);">Нікнейм</label>
                        <input type="text" name="nickname" value="<?= htmlspecialchars($user['nickname'] ?? '') ?>" placeholder="Необов'язково" style="margin-top: 6px;">
                    </div>
                    <button type="submit" style="width: auto; padding: 11px 20px; margin-bottom: 0; flex-shrink: 0;">Зберегти</button>
                </form>

                <div style="margin-top: 14px;">
                    <button type="button" id="openPasswordModal" style="width: auto; padding: 10px 20px; background: var(--surface-2); color: var(--text); border: 1px solid var(--border-solid); font-size: 13px;">🔒 Змінити пароль</button>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: #ff3b30; font-size: 13px; margin-top: 10px;">⚠ <?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 20px;">
            <div class="stat-card stat-mini-card">
                <p>Всього створено</p>
                <h2 style="font-size: 32px; margin: 5px 0;"><?= count($userQrs) ?> <span style="font-size: 18px; color: var(--text-2);">шт.</span></h2>
            </div>
            <div class="stat-card stat-mini-card">
                <p>Остання активність</p>
                <h2 style="font-size: 18px;"><?= $userQrs[0]['created_at'] ?? 'Немає даних' ?></h2>
            </div>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 5px;">
                <h3 style="margin: 0; font-weight: 600;">Мої QR-коди</h3>

                <div style="display: flex; align-items: center; gap: 15px;">
                    <button id="delete-selected" class="apple-link" style="display: none; color: #ff3b30; background: rgba(255, 59, 48, 0.1); border: none; padding: 8px 15px; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                        🗑 Видалити обрані (<span id="selected-count">0</span>)
                    </button>

                    <div class="view-controls" style="display: flex; gap: 8px;">
                        <button class="view-btn" onclick="setMode('grid')" style="background: transparent; border: none; cursor: pointer; padding: 5px; display: flex; align-items: center;">
                            <img src="/QR-code generator/src/assets/icons/grid.png" alt="Grid" style="width: 22px; height: 22px; opacity: 0.5; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.5'">
                        </button>
                        <button class="view-btn" onclick="setMode('list')" style="background: transparent; border: none; cursor: pointer; padding: 5px; display: flex; align-items: center;">
                            <img src="/QR-code generator/src/assets/icons/list.png" alt="List" style="width: 22px; height: 22px; opacity: 0.5; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.5'">
                        </button>
                    </div>
                </div>
            </div>

            <?php if (empty($userQrs)): ?>
                <p style="text-align: center; color: #86868b; padding: 40px;">У вас ще немає згенерованих QR-кодів</p>
            <?php else: ?>

                <?php $needsCollapse = count($userQrs) > 6; ?>

                <div id="qr-container" class="<?= $needsCollapse ? 'collapsed fade-out' : '' ?>">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                        <tr style="border-bottom: 1px solid #d2d2d7; text-align: left; color: #86868b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 15px 10px; width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
                            </th>
                            <th style="padding: 15px 10px; width: 80px;">Тип</th>
                            <th style="padding: 15px 10px;">Вміст та QR</th>
                            <th style="padding: 15px 10px; width: 140px;">Дата створення</th>
                            <th style="padding: 15px 10px; text-align: right; width: 120px;">Дії</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($userQrs as $qr): ?>
                            <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" onmouseover="this.style.background='var(--surface-hover)'" onmouseout="this.style.background='transparent'">

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
                                        <div style="position: relative; width: 44px; height: 44px; flex-shrink: 0; background: var(--surface); padding: 2px; border-radius: 8px; border: 1px solid var(--border-solid); box-shadow: var(--shadow-sm);">
                                            <img src="/QR-code generator/public/<?= htmlspecialchars($qr['svg_path'] ?? $qr['media_path']) ?>"
                                                 style="width: 100%; height: 100%; border-radius: 4px; object-fit: contain;"
                                                 alt="Mini QR">

                                            <div style="position: absolute; bottom: -4px; right: -4px; background: var(--surface); border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 1px solid var(--border-solid); box-shadow: var(--shadow-sm);">
                                                <?php if ($qr['qr_type'] === 'image'): ?>🖼️
                                                <?php elseif ($qr['qr_type'] === 'video'): ?>🎥
                                                <?php else: ?>🔗
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div style="overflow: hidden; max-width: 250px;">
                                            <div style="font-weight: 600; font-size: 13px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars(basename($qr['original_url'])) ?>
                                            </div>
                                            <div style="font-size: 11px; color: var(--accent); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($qr['original_url']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td style="padding: 12px 10px; color: var(--text-2); font-size: 13px; white-space: nowrap;">
                                    <?= date('d.m.Y', strtotime($qr['created_at'])) ?>
                                    <div style="font-size: 10px; opacity: 0.7;"><?= date('H:i', strtotime($qr['created_at'])) ?></div>
                                </td>

                                <td style="padding: 12px 10px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
                                        <button class="apple-link view-qr-btn"
                                                style="background: #f5f5f7; border: none; padding: 6px 12px; border-radius: 20px; color: #0071e3; cursor: pointer; font-weight: 500; font-size: 12px; transition: all 0.2s;"
                                                data-path="<?= htmlspecialchars(str_replace('.png', '.svg', $qr['media_path'] ?? '')) ?>"
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
                        </tbody>
                    </table>
                </div>

                <?php if ($needsCollapse): ?>
                    <div class="expand-wrapper">
                        <button id="toggle-btn" class="btn-expand" onclick="toggleExpand()">Показати всі</button>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <p style="color: #27ae60; margin-top: 15px;">✔ Дані успішно оновлено!</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="passwordModal" style="display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center;">
        <div class="card" style="max-width: 380px; width: 90%; padding: 32px; position: relative; margin: 0 auto;">
            <span id="closePasswordModal" style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px; color: var(--text-3);">&times;</span>
            <h3 style="margin-top: 0; font-weight: 600;">Змінити пароль</h3>
            <div id="passwordError" style="display:none; color: var(--danger); font-size: 13px; margin-bottom: 12px; padding: 10px 14px; background: var(--danger-subtle); border-radius: var(--radius-sm);"></div>
            <div id="passwordSuccess" style="display:none; color: #27ae60; font-size: 13px; margin-bottom: 12px; padding: 10px 14px; background: rgba(52,199,89,0.1); border-radius: var(--radius-sm);">✔ Пароль успішно змінено!</div>
            <div class="form-group">
                <label>Новий пароль</label>
                <input type="password" id="modalPassword" placeholder="Мінімум 6 символів">
            </div>
            <div class="form-group">
                <label>Підтвердіть пароль</label>
                <input type="password" id="modalPasswordConfirm" placeholder="Повторіть пароль">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 6px;">
                <button type="button" id="cancelPasswordModal" style="background: var(--surface-2); color: var(--text); border: 1px solid var(--border-solid); width: 40%;">Скасувати</button>
                <button type="button" id="submitPasswordModal" style="width: 60%;">Оновити</button>
            </div>
        </div>
    </div>

    <div id="qrModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center;">
        <div class="card" style="max-width: 400px; text-align:center; padding: 30px; position: relative; margin: 0 auto;">
            <span id="closeModal" style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px; color: var(--text-3);">&times;</span>
            <h3 style="margin-top: 0; font-weight: 600;">Перегляд QR-коду</h3>
            <img id="modalImg" src="" style="width: 250px; height: 250px; margin: 15px auto; display: block; object-fit: contain;">
            <div style="margin-bottom: 20px; padding: 0 10px;">
                <div style="display: inline-flex; align-items: center; justify-content: center; background: #f5f5f7; border: 1px solid #d2d2d7; padding: 8px 12px; border-radius: 20px; max-width: 100%; box-sizing: border-box;">
                    <span id="modalDynamicIcon" style="margin-right: 6px; font-size: 12px;">🔗</span>
                    <a id="modalContentLink" href="" target="_blank" class="apple-link" style="padding: 0; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none;"></a>
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: center; width: 100%; margin-top: 15px;">
                <a href="#" id="modalDownloadPng" download="qrcode.png" style="flex: 1; text-align: center; align-content: center; text-decoration: none; padding: 12px; background: #0071e3; color: white; border-radius: 12px; font-weight: 600; font-size: 13px; transition: 0.2s;">
                    Завантажити PNG
                </a>
                <a href="#" id="modalDownloadSvg" download="qrcode.svg" style="flex: 1; text-align: center; align-content: center; text-decoration: none; padding: 12px; background: #f5f5f7; color: #0071e3; border: 1px solid #d2d2d7; border-radius: 12px; font-weight: 600; font-size: 14px; transition: 0.2s;">
                    Завантажити SVG
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseAppPath = '<?= rtrim(APP_URL, '/') . '/' ?>';

            const deleteBtnSelected = document.getElementById('delete-selected');
            const countSpan = document.getElementById('selected-count');
            const selectAll = document.getElementById('selectAll');


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
                if (e.target && e.target.classList.contains('view-qr-btn')) {
                    var modal = document.getElementById('qrModal');
                    var modalImg = document.getElementById('modalImg');
                    var modalContentLink = document.getElementById('modalContentLink');
                    var dynIcon = document.getElementById('modalDynamicIcon');

                    var rawPath    = e.target.getAttribute('data-path');
                    var contentUrl = e.target.getAttribute('data-content');
                    var qrType     = e.target.getAttribute('data-type');
                    var baseAppPath = '/QR-code generator/public/';

                    if (rawPath && rawPath.endsWith('.png')) {
                        rawPath = rawPath.replace('.png', '.svg');
                    }

                    var fullPath   = baseAppPath + rawPath;

                    if (modalImg) modalImg.src = fullPath;
                    if (modalContentLink) modalContentLink.innerText = contentUrl;

                    var iconMap = {image:'🖼️', video:'🎥', pdf:'📄', text:'📝', wifi:'📶', call:'📞', vcard:'👤'};
                    if (dynIcon) dynIcon.innerText = iconMap[qrType] || '🔗';

                    if (contentUrl && (contentUrl.indexOf('http://') === 0 || contentUrl.indexOf('https://') === 0)) {
                        modalContentLink.href = contentUrl;
                        modalContentLink.style.pointerEvents = 'auto';
                        modalContentLink.style.color = '#0071e3';
                    } else {
                        modalContentLink.href = '#';
                        modalContentLink.style.pointerEvents = 'none';
                        modalContentLink.style.color = '#1d1d1f';
                    }

                    var modalDownloadPngBtn = document.getElementById('modalDownloadPng');
                    if (modalDownloadPngBtn) {
                        modalDownloadPngBtn.href = fullPath.replace('.svg', '.png');
                    }

                    var modalDownloadSvgBtn = document.getElementById('modalDownloadSvg');
                    if (modalDownloadSvgBtn) {
                        modalDownloadSvgBtn.href = fullPath;
                    }

                    if (modal) modal.style.display = 'flex';
                }

                if (e.target && e.target.id === 'closeModal') {
                    var modal = document.getElementById('qrModal');
                    if (modal) modal.style.display = 'none';
                }
            });

            window.addEventListener('click', function(e) {
                var modal = document.getElementById('qrModal');
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        function setMode(mode) {
            const container = document.getElementById('qr-container');
            if (!container) return;
            if (mode === 'grid') {
                container.classList.add('qr-grid-mode');
            } else {
                container.classList.remove('qr-grid-mode');
            }
        }

        function toggleExpand() {
            const container = document.getElementById('qr-container');
            const btn = document.getElementById('toggle-btn');
            if (!container || !btn) return;

            const isCollapsing = !container.classList.contains('collapsed');

            container.classList.toggle('collapsed');
            container.classList.toggle('fade-out');

            if (container.classList.contains('collapsed')) {
                btn.innerText = 'Показати всі';
            } else {
                btn.innerText = 'Згорнути';
            }

            if (isCollapsing) {
                container.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        const passwordModal   = document.getElementById('passwordModal');
        const openPasswordBtn  = document.getElementById('openPasswordModal');
        const closePasswordBtn = document.getElementById('closePasswordModal');
        const cancelPasswordBtn= document.getElementById('cancelPasswordModal');
        const submitPasswordBtn= document.getElementById('submitPasswordModal');
        const passwordError    = document.getElementById('passwordError');
        const passwordSuccess  = document.getElementById('passwordSuccess');

        function openPasswordModal() {
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalPasswordConfirm').value = '';
            passwordError.style.display   = 'none';
            passwordSuccess.style.display = 'none';
            passwordModal.style.display   = 'flex';
        }
        function closePasswordModalFn() { passwordModal.style.display = 'none'; }

        if (openPasswordBtn)   openPasswordBtn.addEventListener('click', openPasswordModal);
        if (closePasswordBtn)  closePasswordBtn.addEventListener('click', closePasswordModalFn);
        if (cancelPasswordBtn) cancelPasswordBtn.addEventListener('click', closePasswordModalFn);
        window.addEventListener('click', function(e) { if (e.target === passwordModal) closePasswordModalFn(); });

        if (submitPasswordBtn) {
            submitPasswordBtn.addEventListener('click', function() {
                const password = document.getElementById('modalPassword').value;
                const confirm  = document.getElementById('modalPasswordConfirm').value;
                passwordError.style.display   = 'none';
                passwordSuccess.style.display = 'none';

                const formData = new FormData();
                formData.append('password', password);
                formData.append('password_confirm', confirm);

                fetch('/QR-code generator/public/profile/update-password', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            passwordSuccess.style.display = 'block';
                            setTimeout(closePasswordModalFn, 1500);
                        } else {
                            passwordError.textContent     = data.message || 'Помилка';
                            passwordError.style.display   = 'block';
                        }
                    })
                    .catch(function() {
                        passwordError.textContent   = 'Помилка з\'єднання';
                        passwordError.style.display = 'block';
                    });
            });
        }
    </script>

    <footer class="site-footer-bar">
        © 2026 QR Code Generator · Powered by <a href="#" tabindex="-1">naposh</a>
    </footer>
</body>
</html>