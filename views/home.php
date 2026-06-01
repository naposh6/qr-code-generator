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
    <style>
        .stepper-timeline {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step-indicator {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-weight: 500;
            color: #86868b;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            color: #0071e3;
            font-weight: 600;
            border-bottom: 3px solid #0071e3;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .pagination-btn {
            background: #f5f5f7; color: #1d1d1f; border: 1px solid #d2d2d7;
            padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s;
        }
        .pagination-btn:hover:not(:disabled) { background: #e8e8ed; }
        .pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>

<script src="/QR-code generator/public/js/theme.js" defer></script>
<body>
<div class="container">

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

    <div class="card main-generator-card">
        <h1>Створення QR-коду</h1>

        <div class="stepper-timeline">
            <div class="step-indicator active" id="step-1-indicator">Вміст</div>
            <div class="step-indicator" id="step-2-indicator">Стилізація</div>
            <div class="step-indicator" id="step-3-indicator">Результат</div>
        </div>

        <form id="ajaxQrForm" enctype="multipart/form-data">
            <div id="step-1-content" class="step-section">
                <label class="field-section-title" style="display: block; margin-bottom: 15px; font-weight: 600; color: #86868b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Оберіть тип вмісту</label>

                <div class="qr-types-selector-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-bottom: 25px;">
                    <div class="content-type-card active" data-type="url" style="border: 2px solid #0071e3; background: rgba(0,113,227,0.04); padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">Посилання</span>
                    </div>
                    <div class="content-type-card" data-type="text" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">Текст</span>
                    </div>
                    <div class="content-type-card" data-type="wifi" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">Wi-Fi мережа</span>
                    </div>
                    <div class="content-type-card" data-type="image" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">Зображення</span>
                    </div>
                    <div class="content-type-card" data-type="video" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">Відео</span>
                    </div>
                    <div class="content-type-card" data-type="pdf" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">PDF Документ</span>
                    </div>
                    <div class="content-type-card" data-type="call" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">Дзвінок</span>
                    </div>
                    <div class="content-type-card" data-type="vcard" style="border: 1px solid #d2d2d7; padding: 18px 10px; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <span class="type-card-title" style="font-size: 13px; font-weight: 500; display: block;">V-Card</span>
                    </div>
                </div>

                <input type="hidden" name="type" id="hiddenTypeInput" value="url">

                <div id="dynamicInputContainer">
                    <div id="textContentDiv" class="form-group">
                        <label id="inputLabel">Введіть посилання</label>
                        <input type="text" name="content" id="mainContentInput" placeholder="https://example.com">
                    </div>
                    <div id="fileContentDiv" class="form-group" style="display: none;">
                        <label id="fileLabel">Оберіть файл</label>
                        <input type="file" name="qr_file" id="mainFileInput">
                    </div>
                    <div id="wifiContentDiv" style="display: none; gap: 15px; margin-bottom: 20px;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label>Назва мережі (SSID)</label>
                            <input type="text" name="wifi_ssid" id="wifiSsidInput" placeholder="Назва точки доступу">
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label>Пароль до мережі</label>
                            <input type="password" name="wifi_password" placeholder="Пароль">
                        </div>
                    </div>
                    <div id="callContentDiv" style="display: none;" class="form-group">
                        <label>Номер телефону</label>
                        <input type="text" name="call_phone" id="callPhoneInput" placeholder="+380XXXXXXXXX">
                    </div>

                    <div id="vcardContentDiv" style="display: none; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Ім'я</label>
                            <input type="text" name="vcard_name" id="vcardNameInput" placeholder="Ім'я та прізвище">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Телефон</label>
                            <input type="text" name="vcard_phone" id="vcardPhoneInput" placeholder="+380XXXXXXXXX">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Назва (необов'язково)</label>
                    <input type="text" name="title" placeholder="Наприклад: Меню ресторану чи Презентація">
                </div>

                <button type="button" onclick="goToStep(2)">Продовжити</button>
            </div>

            <div id="step-2-content" class="step-section" style="display: none;">
                <label class="field-section-title" style="display: block; margin-bottom: 15px; font-weight: 600; color: #86868b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Параметри кастомізації</label>
                <div class="editor-settings" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: var(--apple-gray); border-radius: 16px; padding: 20px; margin-bottom: 25px;">
                    <div class="form-group" style="margin: 0;">
                        <label>Колір коду</label>
                        <input type="color" name="qr_color" value="#000000" style="height: 45px; padding: 5px; cursor: pointer;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Колір фону</label>
                        <input type="color" name="bg_color" value="#ffffff" style="height: 45px; padding: 5px; cursor: pointer;">
                    </div>
                    <div class="form-group" style="margin: 0; grid-column: span 2;">
                        <label>Емблема / Логотип (Центр QR)</label>
                        <input type="file" name="qr_logo" accept="image/*">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Стиль точок</label>
                        <select name="qr_style">
                            <option value="square">Класичні квадрати</option>
                            <option value="circle">Круглі модулі (Dots)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Розмір (px)</label>
                        <select name="qr_size">
                            <option value="200">200x200</option>
                            <option value="400" selected>400x400</option>
                            <option value="600">600x600</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="button" onclick="goToStep(1)" style="background: #e8e8ed; color: #1d1d1f; width: 35%;">Назад</button>
                    <button type="submit" style="width: 65%;">Згенерувати</button>
                </div>
            </div>

            <div id="step-3-content" class="step-section" style="display: none; text-align: center; padding: 20px 0;">
                <div id="qrLoader">
                    <div class="spinner" style="border: 4px solid rgba(0,0,0,0.08); width: 44px; height: 44px; border-radius: 50%; border-left-color: #0071e3; animation: spin 1s linear infinite; margin: 30px auto;"></div>
                    <p style="color: #86868b; font-size: 14px;">Обробка запиту та рендеринг матриці коду...</p>
                </div>

                <div id="qrResult" style="display: none;">
                    <h3 style="margin: 0 0 20px 0; font-weight: 600;">Готово до використання</h3>
                    <div class="result-preview-box" style="background: #fff; padding: 15px; border-radius: 16px; display: inline-block; box-shadow: 0 4px 25px rgba(0,0,0,0.04); border: 1px solid #d2d2d7; margin-bottom: 25px;">
                        <img id="generatedQrImg" class="result-img" src="" style="width: 240px; height: 240px; display: block; object-fit: contain; margin-top: 0;">
                    </div>

                    <div style="margin-bottom: 25px; padding: 0 20px;">
                        <p id="resultDisplayLabel" style="margin: 0 0 8px 0; font-size: 13px; color: #86868b; font-weight: 500;">Вміст QR-коду:</p>
                        <div style="display: inline-flex; align-items: center; justify-content: center; background: #f5f5f7; border: 1px solid #d2d2d7; padding: 10px 16px; border-radius: 30px; max-width: 90%; box-sizing: border-box;">
                            <span id="resultDisplayIcon" style="margin-right: 8px; font-size: 14px; flex-shrink: 0;">🔗</span>
                            <a id="resultDisplayLink" href="" target="_blank" class="apple-link" style="display: inline-block; padding: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; font-weight: 600; text-decoration: none; max-width: 220px;"></a>
                        </div>
                    </div>

                    <div class="result-actions-wrapper" style="display: flex; flex-direction: column; gap: 10px; max-width: 280px; margin: 0 auto;">
                        <a id="downloadQrBtn" href="" download="qr-code.png" style="text-decoration: none;">
                            <button type="button" style="background: #1d1d1f; color: white;">Завантажити PNG</button>
                        </a>
                        <button type="button" onclick="resetGenerator()" style="background: transparent; color: #0071e3; border: none; cursor: pointer; font-weight: 500; font-size: 14px; padding: 10px; width: auto; margin: 0 auto;">Нова генерація</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card recent-qrs-card" style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 style="margin: 0 0 5px 0; font-size: 22px;">Історія генерацій</h3>
                <p style="margin: 0; color: #86868b; font-size: 14px;">Керування та пошук створених QR-кодів</p>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="history-search" placeholder="Пошук за назвою..." style="padding: 9px 14px; border-radius: 10px; border: 1px solid #d2d2d7; font-size: 14px; min-width: 220px; outline: none; transition: border 0.2s;" onfocus="this.style.border='1px solid #0071e3'" onblur="this.style.border='1px solid #d2d2d7'">

                <button id="delete-selected" style="display: none; padding: 9px 16px; font-size: 13px; background: #fff; color: #ff3b30; border: 1px solid #ff3b30; border-radius: 10px; cursor: pointer; font-weight: 600; margin-top: 0;">
                    🗑 Видалити (<span id="selected-count">0</span>)
                </button>

                <a href="/QR-code generator/public/profile" class="apple-link" style="background: #f5f5f7; color: #1d1d1f; border: 1px solid #d2d2d7; padding: 8px 14px; border-radius: 10px; font-size: 13px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; transition: all 0.2s;">Вся історія →</a>
            </div>
        </div>

        <div class="table-container" id="qr-list-container" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                <tr style="border-bottom: 1px solid #d2d2d7;">
                    <th style="padding: 12px; width: 40px;"><input type="checkbox" id="selectAll"></th>
                    <th style="padding: 12px; width: 80px;">Тип</th>
                    <th style="padding: 12px;">QR & Вміст</th>
                    <th style="padding: 12px; width: 120px;">Створено</th>
                    <th style="padding: 12px; text-align: right; width: 100px;">Дії</th>
                </tr>
                </thead>
                <tbody id="history-container">
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #86868b;">Завантаження історії...</td></tr>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: center; margin-top: 25px;">
            <div id="pagination-wrapper" style="display: flex; gap: 6px; align-items: center; background: #f5f5f7; padding: 6px; border-radius: 12px;">
            </div>
        </div>
    </div>
</div>

<div id="qrModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center;">
    <div class="card" style="max-width: 400px; text-align:center; padding: 30px; position: relative; margin: 0 auto;">
        <span id="closeModal" style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px; color: #86868b;">&times;</span>
        <h3 style="margin-top: 0; font-weight: 600;">Перегляд QR-коду</h3>
        <img id="modalImg" src="" style="width: 250px; height: 250px; margin: 15px auto; display: block; object-fit: contain;">

        <div style="margin-bottom: 20px; padding: 0 10px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; background: #f5f5f7; border: 1px solid #d2d2d7; padding: 8px 12px; border-radius: 20px; max-width: 100%; box-sizing: border-box;">
                <span id="modalDynamicIcon" style="margin-right: 6px; font-size: 12px;">🔗</span>
                <a id="modalContentLink" href="" target="_blank" class="apple-link" style="padding: 0; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none;"></a>
            </div>
        </div>

        <a id="modalDownload" href="" download="qr-code.png" style="text-decoration: none;">
            <button type="button" style="background: #1d1d1f; color: #fff;">Завантажити PNG</button>
        </a>
    </div>
</div>

<script>
    const baseAppPath = '/QR-code generator/public/';

    let currentPage = 1;
    let totalItems = 0;
    const limitPerPage = 5;
    const searchInput = document.getElementById('history-search');
    const historyContainer = document.getElementById('history-container');
    const paginationWrapper = document.getElementById('pagination-wrapper');

    function loadHistory(page = 1, search = '') {
        fetch(baseAppPath + `get-history-ajax?page=${page}&search=${encodeURIComponent(search)}`)
            .then(res => {
                if(!res.ok) throw new Error("Помилка завантаження історії");
                return res.json();
            })
            .then(data => {
                let qrs = Array.isArray(data) ? data : (data.data || []);

                totalItems = data.total !== undefined ? data.total : (qrs.length < limitPerPage ? (page - 1) * limitPerPage + qrs.length : page * limitPerPage + 1);

                if (qrs.length === 0) {
                    historyContainer.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 30px; color: #86868b;">Нічого не знайдено</td></tr>`;
                    paginationWrapper.innerHTML = '';
                    return;
                }

                historyContainer.innerHTML = qrs.map(qr => {
                    let displayTitle = qr.title || '';
                    let displaySub = '';
                    let icon = '🔗';
                    let targetUrl = qr.original_url;
                    let shortLink = qr.short_code ? (window.location.protocol + '//' + window.location.host + baseAppPath + 'r/' + qr.short_code) : '';

                    if (qr.qr_type === 'url') {
                        displayTitle = displayTitle || qr.original_url;
                        displaySub = qr.original_url;
                    } else if (qr.qr_type === 'text') {
                        displayTitle = displayTitle || (qr.original_url.substring(0, 40) + "...");
                        displaySub = "Текстові дані";
                        icon = '📝';
                    } else if (qr.qr_type === 'wifi') {
                        displayTitle = displayTitle || "Wi-Fi Мережа";
                        let match = qr.original_url.match(/S:(.*?);/);
                        displaySub = "SSID: " + (match ? match[1] : 'Прихована мережа');
                        icon = '📶';
                    } else if (qr.qr_type === 'call') {
                        displayTitle = displayTitle || 'Дзвінок';
                        displaySub = qr.original_url; // "tel:+380..."
                        icon = '📞';
                    } else if (qr.qr_type === 'vcard') {
                        displayTitle = displayTitle || 'Контакт vCard';
                        let fnMatch = qr.original_url.match(/FN:(.*)/);
                        displaySub = fnMatch ? fnMatch[1].trim() : 'Контактна картка';
                        icon = '👤';
                    } else if (['image', 'video', 'pdf'].includes(qr.qr_type)) {
                        displayTitle = displayTitle || `Медіафайл (${qr.qr_type.toUpperCase()})`;
                        displaySub = shortLink || 'Медіафайл';
                        if (qr.qr_type === 'image') icon = '🖼️';
                        if (qr.qr_type === 'video') icon = '🎥';
                        if (qr.qr_type === 'pdf') icon = '📄';
                        targetUrl = shortLink || qr.original_url;
                    }

                    let dateObj = new Date(qr.created_at);
                    let dateStr = dateObj.toLocaleDateString('uk-UA', {day: '2-digit', month: '2-digit', year: 'numeric'});

                    return `
                    <tr id="qr-row-${qr.id}" style="border-bottom: 1px solid #e8e8ed;">
                        <td style="padding: 12px;"><input type="checkbox" class="qr-checkbox" value="${qr.id}"></td>
                        <td style="padding: 12px;">
                            <span class="badge" style="background: #e8e8ed; color: #1d1d1f; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
                                ${qr.qr_type.toUpperCase()}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="table-qr-preview-box" style="width: 46px; height: 46px; background: #fff; border-radius: 10px; padding: 3px; border: 1px solid #d2d2d7; box-shadow: 0 2px 5px rgba(0,0,0,0.04); flex-shrink: 0; position: relative;">
                                    <img src="${baseAppPath}${qr.media_path}" style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px;">
                                    <div style="position: absolute; bottom: -4px; right: -4px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 1px solid #d2d2d7; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        ${icon}
                                    </div>
                                </div>
                                <div style="max-width: 250px; overflow: hidden;">
                                    <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1d1d1f;">
                                        ${displayTitle}
                                    </div>
                                    <div style="font-size: 12px; color: #0071e3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ${displaySub}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px; color: #86868b; font-size: 13px;">${dateStr}</td>
                        <td style="padding: 12px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                <button class="view-qr-btn"
                                        data-path="${qr.media_path}"
                                        data-content="${targetUrl}"
                                        data-type="${qr.qr_type}"
                                        style="padding: 8px 14px; font-size: 12px; background: rgba(0, 113, 227, 0.1); color: #0071e3; border: none; border-radius: 12px; cursor: pointer; font-weight: 500; width: auto; margin-top: 0;">
                                    Відкрити
                                </button>
                                <button class="delete-qr-btn" data-id="${qr.id}"
                                        style="background: none; color: #ff3b30; border: none; cursor: pointer; font-size: 20px; padding: 0 5px; width: auto; margin-top: 0;">
                                    &times;
                                </button>
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                const listContainer = document.getElementById('qr-list-container');
                if (listContainer) {
                    listContainer.classList.remove('fade-in');
                    void listContainer.offsetWidth;
                    listContainer.classList.add('fade-in');
                }

                const tableWrapper = document.querySelector('.table-container');
                if (tableWrapper) {
                    const yOffset = -20;
                    const y = tableWrapper.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({top: y, behavior: 'smooth'});
                }

                currentPage = page;
                renderPagination(qrs.length);

                const selectAllBtn = document.getElementById('selectAll');
                if(selectAllBtn) selectAllBtn.checked = false;
                updateBulkButton();
            })
            .catch(err => {
                console.error(err);
                historyContainer.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: #ff3b30;">Помилка завантаження даних</td></tr>`;
            });
        document.querySelector('.table-container').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    function renderPagination(currentRowsCount) {
        const totalPages = Math.ceil(totalItems / limitPerPage);
        let html = '';

        html += `<button title="На початок" class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(1)">«</button>`;
        html += `<button title="Назад" class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">‹</button>`;

        let startPage = Math.max(1, currentPage - 1);
        let endPage = startPage + 2;

        if (endPage > totalPages) {
            endPage = totalPages;
            startPage = Math.max(1, endPage - 2);
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += `<button class="pagination-btn active" disabled>${i}</button>`;
            } else {
                html += `<button class="pagination-btn" onclick="changePage(${i})">${i}</button>`;
            }
        }

        const isLastPage = currentPage >= totalPages || currentRowsCount < limitPerPage;
        html += `<button title="Вперед" class="pagination-btn" ${isLastPage ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">›</button>`;
        html += `<button title="В кінець" class="pagination-btn" ${isLastPage ? 'disabled' : ''} onclick="changePage(${totalPages})">»</button>`;

        paginationWrapper.innerHTML = html;
    }

    window.changePage = function(page) {
        loadHistory(page, searchInput.value);
    };

    let typingTimer;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            loadHistory(1, e.target.value);
        }, 400);
    });

    loadHistory(1, '');

    function goToStep(stepNum) {
        document.querySelectorAll('.step-section').forEach(el => el.style.display = 'none');
        document.getElementById(`step-${stepNum}-content`).style.display = 'block';

        document.querySelectorAll('.step-indicator').forEach((el, index) => {
            if (index + 1 === stepNum) el.classList.add('active');
            else el.classList.remove('active');
        });
    }

    function resetGenerator() {
        document.getElementById('ajaxQrForm').reset();
        document.querySelectorAll('.content-type-card').forEach(c => {
            c.style.border = '1px solid #d2d2d7';
            c.style.background = 'transparent';
        });
        const defaultCard = document.querySelector('.content-type-card[data-type="url"]');
        defaultCard.style.border = '2px solid #0071e3';
        defaultCard.style.background = 'rgba(0,113,227,0.04)';
        document.getElementById('hiddenTypeInput').value = 'url';

        handleTypeChange('url');

        document.getElementById('qrLoader').style.display = 'block';
        document.getElementById('qrResult').style.display = 'none';
        goToStep(1);
    }

    document.querySelectorAll('.content-type-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.content-type-card').forEach(c => {
                c.style.border = '1px solid #d2d2d7';
                c.style.background = 'transparent';
            });
            this.style.border = '2px solid #0071e3';
            this.style.background = 'rgba(0,113,227,0.04)';

            const selectedType = this.getAttribute('data-type');
            document.getElementById('hiddenTypeInput').value = selectedType;

            handleTypeChange(selectedType);
        });
    });

    function handleTypeChange(type) {
        const textDiv  = document.getElementById('textContentDiv');
        const fileDiv  = document.getElementById('fileContentDiv');
        const wifiDiv  = document.getElementById('wifiContentDiv');
        const callDiv  = document.getElementById('callContentDiv');
        const vcardDiv = document.getElementById('vcardContentDiv');
        const label     = document.getElementById('inputLabel');
        const fileLabel = document.getElementById('fileLabel');

        // Ховаємо всі блоки
        textDiv.style.display  = 'none';
        fileDiv.style.display  = 'none';
        wifiDiv.style.display  = 'none';
        callDiv.style.display  = 'none';
        vcardDiv.style.display = 'none';

        if (type === 'url') {
            textDiv.style.display = 'block'; label.innerText = 'Введіть посилання';
            document.getElementById('mainContentInput').placeholder = 'https://example.com';
        } else if (type === 'text') {
            textDiv.style.display = 'block'; label.innerText = 'Введіть текстове повідомлення';
            document.getElementById('mainContentInput').placeholder = 'Ваш текст тут...';
        } else if (type === 'wifi') {
            wifiDiv.style.display = 'flex';
        } else if (type === 'call') {
            callDiv.style.display = 'block';
        } else if (type === 'vcard') {
            vcardDiv.style.display = 'flex';
        } else if (type === 'image') {
            fileDiv.style.display = 'block'; fileLabel.innerText = 'Оберіть зображення (PNG/JPG)';
            document.getElementById('mainFileInput').accept = 'image/*';
        } else if (type === 'video') {
            fileDiv.style.display = 'block'; fileLabel.innerText = 'Оберіть відеоролик (MP4)';
            document.getElementById('mainFileInput').accept = 'video/*';
        } else if (type === 'pdf') {
            fileDiv.style.display = 'block'; fileLabel.innerText = 'Оберіть файл (PDF)';
            document.getElementById('mainFileInput').accept = '.pdf,application/pdf';
        }
    }

    document.getElementById('ajaxQrForm').addEventListener('submit', function(e) {
        e.preventDefault();
        goToStep(3);

        const formData = new FormData(this);

        fetch(baseAppPath + 'generate', { method: 'POST', body: formData })
            .then(response => {
                if (!response.ok) throw new Error('Сервер повернув помилку статусу');
                return response.json();
            })
            .then(data => {
                if (data && data.media_path) {
                    document.getElementById('qrLoader').style.display = 'none';
                    document.getElementById('qrResult').style.display = 'block';

                    const fullQrPath = baseAppPath + data.media_path;
                    document.getElementById('generatedQrImg').src = fullQrPath;
                    document.getElementById('downloadQrBtn').href = fullQrPath;

                    const displayLink = document.getElementById('resultDisplayLink');
                    const displayLabel = document.getElementById('resultDisplayLabel');
                    const displayIcon = document.getElementById('resultDisplayIcon');
                    const type = document.getElementById('hiddenTypeInput').value;

                    let targetUrl = data.original_url || data.content || data.url;

                    if (!targetUrl) {
                        if (type === 'url' || type === 'text') targetUrl = document.getElementById('mainContentInput').value;
                        else if (type === 'wifi') targetUrl = 'SSID: ' + (document.getElementById('wifiSsidInput').value || 'Мережа');
                        else if (type === 'call')  targetUrl = document.getElementById('callPhoneInput').value;
                        else if (type === 'vcard') targetUrl = document.getElementById('vcardNameInput').value + ' / ' + document.getElementById('vcardPhoneInput').value;
                    }

                    if (type === 'url') {
                        displayLabel.innerText = 'Вміст QR-коду (посилання):'; displayIcon.innerText = '🔗';
                    } else if (['image', 'video', 'pdf'].includes(type)) {
                        displayLabel.innerText = 'Коротке посилання на медіафайл:';
                        displayIcon.innerText = type === 'image' ? '🖼️' : (type === 'video' ? '🎥' : '📄');
                        if (data.short_code) targetUrl = window.location.protocol + '//' + window.location.host + baseAppPath + 'r/' + data.short_code;
                    } else if (type === 'text') {
                        displayLabel.innerText = 'Вміст QR-коду (текст):'; displayIcon.innerText = '📝';
                    } else if (type === 'wifi') {
                        displayLabel.innerText = 'Дані Wi-Fi мережі:'; displayIcon.innerText = '📶';
                    } else if (type === 'call') {
                        displayLabel.innerText = 'Телефонний номер для дзвінка:'; displayIcon.innerText = '📞';
                    } else if (type === 'vcard') {
                        displayLabel.innerText = 'Контактна картка vCard:'; displayIcon.innerText = '👤';
                    }

                    if (targetUrl) {
                        displayLink.innerText = targetUrl;
                        if (targetUrl.startsWith('http')) {
                            displayLink.href = targetUrl; displayLink.style.pointerEvents = 'auto'; displayLink.style.color = '#0071e3';
                        } else {
                            displayLink.href = '#'; displayLink.style.pointerEvents = 'none'; displayLink.style.color = '#1d1d1f';
                        }
                    }

                    loadHistory(1, '');

                } else {
                    alert('Не вдалося згенерувати код: ' + (data.message || 'Помилка валідації даних'));
                    goToStep(2);
                }
            })
            .catch(err => {
                console.error(err); alert('Помилка асинхронного з’єднання з сервером.'); goToStep(2);
            });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('qrModal');
        const modalImg = document.getElementById('modalImg');
        const modalContentLink = document.getElementById('modalContentLink');
        const modalDownload = document.getElementById('modalDownload');

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('view-qr-btn')) {
                const rawPath = e.target.getAttribute('data-path');
                const contentUrl = e.target.getAttribute('data-content');
                const qrType = e.target.getAttribute('data-type');
                const fullPath = baseAppPath + rawPath;

                modalImg.src = fullPath;
                modalContentLink.innerText = contentUrl;

                const dynamicIcon = document.getElementById('modalDynamicIcon');
                if (dynamicIcon) {
                    if (qrType === 'image') dynamicIcon.innerText = '🖼️';
                    else if (qrType === 'video') dynamicIcon.innerText = '🎥';
                    else if (qrType === 'pdf') dynamicIcon.innerText = '📄';
                    else if (qrType === 'text') dynamicIcon.innerText = '📝';
                    else if (qrType === 'wifi') dynamicIcon.innerText = '📶';
                    else if (qrType === 'call') dynamicIcon.innerText = '📞';
                    else if (qrType === 'vcard') dynamicIcon.innerText = '👤';
                    else dynamicIcon.innerText = '🔗';
                }

                if (contentUrl.startsWith('http://') || contentUrl.startsWith('https://')) {
                    modalContentLink.href = contentUrl;
                    modalContentLink.style.pointerEvents = 'auto';
                    modalContentLink.style.color = '#0071e3';
                } else {
                    modalContentLink.href = '#';
                    modalContentLink.style.pointerEvents = 'none';
                    modalContentLink.style.color = '#1d1d1f';
                }

                modalDownload.href = fullPath;
                modal.style.display = 'flex';
            }
        });

        document.getElementById('closeModal').onclick = () => modal.style.display = 'none';
        window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; }
    });

    function updateBulkButton() {
        const selected = document.querySelectorAll('.qr-checkbox:checked').length;
        const deleteBtnSelected = document.getElementById('delete-selected');
        const countSpan = document.getElementById('selected-count');
        if(deleteBtnSelected) {
            deleteBtnSelected.style.display = selected > 0 ? 'block' : 'none';
            countSpan.innerText = selected;
        }
    }

    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectAll') {
            document.querySelectorAll('.qr-checkbox').forEach(cb => cb.checked = e.target.checked);
            updateBulkButton();
        } else if (e.target.classList.contains('qr-checkbox')) {
            updateBulkButton();
        }
    });

    function sendDeleteRequest(ids) {
        if (!confirm(`Видалити обрані об'єкти (${ids.length} шт.)?`)) return;

        fetch(baseAppPath + 'delete-qrs', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        })
            .then(res => res.json())
            .then(() => {
                loadHistory(currentPage, searchInput.value);
            });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-qr-btn')) {
            sendDeleteRequest([e.target.getAttribute('data-id')]);
        }
        if (e.target.id === 'delete-selected') {
            const ids = Array.from(document.querySelectorAll('.qr-checkbox:checked')).map(cb => cb.value);
            sendDeleteRequest(ids);
        }
    });
</script>
</body>
</html>