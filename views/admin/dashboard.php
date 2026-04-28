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

        document.getElementById('load-qrs').addEventListener('click', () => {
            loadData('admin/get-qrs-ajax');
        });

        document.getElementById('load-users').addEventListener('click', () => {
            loadData('admin/get-users-ajax');
        });
    });
</script>
</body>
</html>