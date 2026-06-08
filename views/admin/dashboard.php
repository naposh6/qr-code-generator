<?php
/** @var array $allUsers */
/** @var array $stats */
$appUrl = rtrim(ASSETS_URL, '/');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель</title>
    <link rel="icon" type="image/png" href="<?= $appUrl ?>/assets/logo-qr.png">
    <link rel="stylesheet" href="<?= $appUrl ?>/css/style.css">
</head>
<script src="<?= $appUrl ?>/js/theme.js" defer></script>
<body>
<div class="container">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:40px;">
        <h1 style="margin:0;font-weight:700;letter-spacing:-0.5px;">Керування системою</h1>
        <a href="<?= BASE_DIR ?>/" class="apple-link secondary">← На головну</a>
    </header>

    <div class="stats-grid">
        <div class="card stat-card" id="load-users" style="cursor:pointer;">
            <span style="color:var(--text-2);font-size:14px;font-weight:600;">КОРИСТУВАЧІ</span>
            <h2 style="font-size:32px;margin:10px 0;color:var(--accent);"><?= (int)$stats['total_users'] ?></h2>
        </div>
        <div class="card stat-card" id="load-qrs" style="cursor:pointer;">
            <span style="color:var(--text-2);font-size:14px;font-weight:600;">QR-КОДИ</span>
            <h2 style="font-size:32px;margin:10px 0;color:var(--accent);"><?= (int)$stats['total_qrs'] ?></h2>
        </div>
    </div>

    <div class="card" id="dynamic-content" style="margin-top:30px;overflow-x:auto;transition:opacity 0.3s ease;">
        <?php include __DIR__ . '/_users_table.php'; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contentArea = document.getElementById('dynamic-content');
    const baseAppPath = '<?= $appUrl ?>/';

    function loadData(url) {
        contentArea.style.opacity = '0.3';
        fetch(url)
            .then(r => r.text())
            .then(html => { contentArea.innerHTML = html; contentArea.style.opacity = '1'; })
            .catch(() => { contentArea.style.opacity = '1'; });
    }

    document.getElementById('load-qrs').addEventListener('click',   () => loadData('<?= BASE_DIR ?>/admin/get-qrs-ajax'));
    document.getElementById('load-users').addEventListener('click',  () => loadData('<?= BASE_DIR ?>/admin/get-users-ajax'));

    // Modal: view QR
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('view-qr-btn')) {
            var rawPath    = e.target.getAttribute('data-path');
            var contentUrl = e.target.getAttribute('data-content');
            var qrType     = e.target.getAttribute('data-type');
            var modal      = document.getElementById('qrModal');
            var modalImg   = document.getElementById('modalImg');
            var modalContentLink = document.getElementById('modalContentLink');
            var dynIcon    = document.getElementById('modalDynamicIcon');

            if (rawPath && rawPath.endsWith('.png')) rawPath = rawPath.replace('.png', '.svg');
            var fullPath = baseAppPath + rawPath;

            modalImg.src = fullPath;
            if (modalContentLink) modalContentLink.innerText = contentUrl;

            var iconMap = {image:'🖼️',video:'🎥',pdf:'📄',text:'📝',wifi:'📶',call:'📞',vcard:'👤'};
            if (dynIcon) dynIcon.innerText = iconMap[qrType] || '🔗';

            if (contentUrl && (contentUrl.indexOf('http://') === 0 || contentUrl.indexOf('https://') === 0)) {
                modalContentLink.href = contentUrl;
                modalContentLink.style.pointerEvents = 'auto';
                modalContentLink.style.color = 'var(--accent)';
            } else {
                modalContentLink.href = '#';
                modalContentLink.style.pointerEvents = 'none';
                modalContentLink.style.color = 'var(--text)';
            }

            var pngBtn = document.getElementById('modalDownloadPng');
            if (pngBtn) pngBtn.href = fullPath.replace('.svg', '.png');
            var svgBtn = document.getElementById('modalDownloadSvg');
            if (svgBtn) svgBtn.href = fullPath;

            modal.style.display = 'flex';
        }

        if (e.target && (e.target.id === 'closeModal' || e.target.id === 'qrModal')) {
            document.getElementById('qrModal').style.display = 'none';
        }
    });

    // Checkboxes
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'selectAll') {
            document.querySelectorAll('.qr-checkbox').forEach(cb => cb.checked = e.target.checked);
            updateDeleteBtn();
        }
        if (e.target && e.target.classList.contains('qr-checkbox')) updateDeleteBtn();
    });

    function updateDeleteBtn() {
        var sel     = document.querySelectorAll('.qr-checkbox:checked');
        var btn     = document.getElementById('delete-selected');
        var count   = document.getElementById('selected-count');
        if (sel.length > 0) { btn.style.display = 'block'; count.innerText = sel.length; }
        else btn.style.display = 'none';
    }

    // Delete
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('delete-qr-btn')) {
            if (confirm('Видалити цей QR-код?')) sendDeleteRequest([e.target.getAttribute('data-id')]);
        }
        if (e.target && e.target.id === 'delete-selected') {
            var ids = Array.from(document.querySelectorAll('.qr-checkbox:checked')).map(cb => cb.value);
            if (confirm('Видалити обрані QR-коди (' + ids.length + ' шт.)?')) sendDeleteRequest(ids);
        }
    });

    function sendDeleteRequest(ids) {
        fetch('<?= BASE_DIR ?>/admin/delete-qrs', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ids: ids})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadData('<?= BASE_DIR ?>/admin/get-qrs-ajax');
            else alert('Помилка: ' + (data.message || '?'));
        });
    }
});

function setMode(mode) {
    const c = document.getElementById('qr-container');
    if (mode === 'grid') c.classList.add('qr-grid-mode');
    else c.classList.remove('qr-grid-mode');
}

function toggleExpand() {
    const c   = document.getElementById('qr-container');
    const btn = document.getElementById('toggle-btn');
    c.classList.toggle('collapsed');
    c.classList.toggle('fade-out');
    btn.innerText = c.classList.contains('collapsed') ? 'Показати всі' : 'Згорнути';
    if (c.classList.contains('collapsed')) c.scrollIntoView({behavior:'smooth', block:'start'});
}

window.addEventListener('click', function (e) {
    var modal = document.getElementById('qrModal');
    if (e.target === modal) modal.style.display = 'none';
});
</script>

<!-- QR Modal -->
<div id="qrModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);backdrop-filter:blur(8px);align-items:center;justify-content:center;">
    <div class="card" style="max-width:400px;text-align:center;padding:30px;position:relative;margin:0 auto;">
        <span id="closeModal" style="position:absolute;right:20px;top:15px;cursor:pointer;font-size:24px;color:var(--text-3);">&times;</span>
        <h3 style="margin-top:0;font-weight:600;">Перегляд QR-коду</h3>
        <img id="modalImg" src="" style="width:250px;height:250px;margin:15px auto;display:block;object-fit:contain;">
        <div style="margin-bottom:20px;padding:0 10px;">
            <div style="display:inline-flex;align-items:center;justify-content:center;background:var(--surface-2);border:1px solid var(--border-solid);padding:8px 12px;border-radius:20px;max-width:100%;box-sizing:border-box;">
                <span id="modalDynamicIcon" style="margin-right:6px;font-size:12px;">🔗</span>
                <a id="modalContentLink" href="" target="_blank" class="apple-link" style="padding:0;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;"></a>
            </div>
        </div>
        <div style="display:flex;gap:10px;justify-content:center;width:100%;margin-top:15px;">
            <a href="#" id="modalDownloadPng" download="qrcode.png" style="flex:1;text-align:center;align-content:center;text-decoration:none;padding:12px;background:var(--accent);color:white;border-radius:12px;font-weight:600;font-size:13px;">Завантажити PNG</a>
            <a href="#" id="modalDownloadSvg" download="qrcode.svg" style="flex:1;text-align:center;align-content:center;text-decoration:none;padding:12px;background:var(--surface-2);color:var(--accent);border:1px solid var(--border-solid);border-radius:12px;font-weight:600;font-size:14px;">Завантажити SVG</a>
        </div>
    </div>
</div>

<footer class="site-footer-bar">
    © 2026 QR Code Generator · Powered by <a href="#" tabindex="-1">naposh</a>
</footer>
</body>
</html>
