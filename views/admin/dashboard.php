<?php
/** @var array $allUsers */
/** @var array $stats */
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін-панель</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>

<script src="/QR-code generator/public/js/theme.js" defer></script>
<body>
<div class="container">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <h1 style="margin: 0; font-weight: 700; letter-spacing: -0.5px;">Керування системою</h1>
        <a href="/QR-code generator/public/" class="apple-link secondary">← На головну</a>
    </header>

    <div class="stats-grid">
        <div class="card stat-card" id="load-users" style="cursor: pointer;">
            <span style="color: #86868b; font-size: 14px; font-weight: 600;">КОРИСТУВАЧІ</span>
            <h2 style="font-size: 32px; margin: 10px 0; color: #0071e3;"><?= $stats['total_users'] ?></h2>
        </div>

        <div class="card stat-card" id="load-qrs" style="cursor: pointer;">
            <span style="color: #86868b; font-size: 14px; font-weight: 600;">QR-КОДИ</span>
            <h2 style="font-size: 32px; margin: 10px 0; color: #0071e3;"><?= $stats['total_qrs'] ?></h2>
        </div>
    </div>

    <div class="card" id="dynamic-content" style="margin-top: 30px; overflow-x: auto; transition: opacity 0.3s ease;">
        <?php include __DIR__ . '/_users_table.php'; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contentArea = document.getElementById('dynamic-content');
        const modal = document.getElementById('qrModal');
        const modalImg = document.getElementById('modalImg');
        const modalContent = document.getElementById('modalContent');
        const modalDownload = document.getElementById('modalDownload');
        const baseAppPath = '/QR-code generator/public/';

        function loadData(url) {
            contentArea.style.opacity = '0.3';
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    contentArea.innerHTML = html;
                    contentArea.style.opacity = '1';
                })
                .catch(err => {
                    console.error('Помилка завантаження:', err);
                    contentArea.style.opacity = '1';
                });
        }

        document.getElementById('load-qrs').addEventListener('click', () => loadData('admin/get-qrs-ajax'));
        document.getElementById('load-users').addEventListener('click', () => loadData('admin/get-users-ajax'));

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('view-qr-btn')) {
                const rawPath = e.target.getAttribute('data-path');
                const content = e.target.getAttribute('data-content');

                if (!rawPath) {
                    alert('Зображення відсутнє для цього запису');
                    return;
                }

                const fullPath = baseAppPath + rawPath;

                const imgCheck = new Image();
                imgCheck.onload = function() {
                    modalImg.src = fullPath;
                    modalContent.innerText = content;
                    modalDownload.href = fullPath;
                    modalDownload.setAttribute('download', 'qr-code.png');
                    modal.style.display = 'flex';
                };
                imgCheck.onerror = function() {
                    alert('Файл не знайдено за шляхом: ' + fullPath);
                };
                imgCheck.src = fullPath;
            }

            if (e.target && (e.target.id === 'closeModal' || e.target.id === 'qrModal')) {
                modal.style.display = 'none';
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'selectAll') {
                const checkboxes = document.querySelectorAll('.qr-checkbox');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateDeleteButtonVisibility();
            }
            if (e.target && e.target.classList.contains('qr-checkbox')) {
                updateDeleteButtonVisibility();
            }
        });

        function updateDeleteButtonVisibility() {
            const selected = document.querySelectorAll('.qr-checkbox:checked');
            const deleteBtn = document.getElementById('delete-selected');
            const countSpan = document.getElementById('selected-count');

            if (selected.length > 0) {
                deleteBtn.style.display = 'block';
                countSpan.innerText = selected.length;
            } else {
                deleteBtn.style.display = 'none';
            }
        }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('delete-qr-btn')) {
                if (confirm('Видалити цей QR-код?')) {
                    const id = e.target.getAttribute('data-id');
                    sendDeleteRequest([id]);
                }
            }

            if (e.target && e.target.id === 'delete-selected') {
                const selected = Array.from(document.querySelectorAll('.qr-checkbox:checked')).map(cb => cb.value);
                if (confirm(`Видалити обрані QR-коди (${selected.length} шт.)?`)) {
                    sendDeleteRequest(selected);
                }
            }
        });

        function sendDeleteRequest(ids) {
            fetch('admin/delete-qrs', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadData('admin/get-qrs-ajax');
                    } else {
                        alert('Помилка видалення: ' + (data.message || 'Невідома помилка'));
                    }
                })
                .catch(err => console.error('Помилка:', err));
        }
    });

    function setMode(mode) {
        const container = document.getElementById('qr-container');
        if (mode === 'grid') {
            container.classList.add('qr-grid-mode');
        } else {
            container.classList.remove('qr-grid-mode');
        }
    }

    function toggleExpand() {
        const container = document.getElementById('qr-container');
        const btn = document.getElementById('toggle-btn');

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

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('view-qr-btn')) {
            var modal = document.getElementById('qrModal');
            var modalImg = document.getElementById('modalImg');
            var modalContentLink = document.getElementById('modalContentLink');
            var modalDownload = document.getElementById('modalDownload');
            var dynIcon = document.getElementById('modalDynamicIcon');

            var rawPath    = e.target.getAttribute('data-path');
            var contentUrl = e.target.getAttribute('data-content');
            var qrType     = e.target.getAttribute('data-type');
            var baseAppPath = '/QR-code generator/public/';
            var fullPath   = baseAppPath + rawPath;

            if (modalImg) modalImg.src = fullPath;
            if (modalContentLink) modalContentLink.innerText = contentUrl;
            if (modalDownload) modalDownload.href = fullPath;

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
</script>

<div id="qrModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(10px); align-items: center; justify-content: center;">
    <div class="card" style="position:relative; max-width: 400px; width: 90%; text-align:center; padding: 40px;">
        <span id="closeModal" style="position:absolute; right:20px; top:10px; cursor:pointer; font-size:28px; color: #86868b;">&times;</span>
        <h3 style="margin-top: 0;"><span id="modalDynamicIcon">🔗</span> Перегляд QR-коду</h3>
        <img id="modalImg" src="" style="max-width:100%; border-radius:12px; border: 1px solid #d2d2d7; margin: 20px 0;">
        <div style="margin-bottom: 20px;">
            <a id="modalContentLink" href="#" target="_blank" style="word-break: break-all; text-decoration: none; font-weight: 500;"></a>
        </div>
        <a id="modalDownload" href="#" class="apple-link" download style="display: inline-block; background: #0071e3; color: #fff; padding: 8px 16px; border-radius: 12px; text-decoration: none; font-weight: 500;">Завантажити</a>
    </div>
</div>
</body>
</html>