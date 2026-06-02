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

        .color-combo {
            display: flex; align-items: center; gap: 8px; margin-top: 6px;
        }
        .color-combo input[type="color"] {
            width: 46px; height: 46px; padding: 3px; flex-shrink: 0; cursor: pointer;
            border-radius: var(--radius-md); border: 1.5px solid var(--input-border);
            background: var(--input-bg); margin-top: 0;
        }
        .color-combo input[type="text"] {
            flex: 1; font-family: 'Courier New', monospace; font-size: 14px;
            letter-spacing: 1px; text-transform: uppercase; margin-top: 0;
        }
        .style-picker {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
            gap: 8px; margin-top: 8px;
        }
        .style-option {
            display: flex; flex-direction: column; align-items: center;
            gap: 5px; padding: 8px 4px; border-radius: var(--radius-md);
            border: 2px solid var(--border-solid); cursor: pointer;
            transition: var(--transition); background: var(--surface-2);
            font-size: 10px; font-weight: 600; color: var(--text-2);
            text-transform: uppercase; letter-spacing: 0.4px; text-align: center;
            user-select: none;
        }
        .style-option:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-1px); }
        .style-option.selected { border-color: var(--accent); background: var(--accent-subtle); color: var(--accent); }
        .style-option svg { display: block; }
        #livePreviewBox {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 14px;
            border: 1px solid var(--border-solid);
            box-shadow: var(--shadow-sm);
            display: flex; align-items: center; justify-content: center;
            min-height: 148px; min-width: 148px;
        }
        #livePreviewSvg svg { width: 120px !important; height: 120px !important; }
        .custom-section {
            background: var(--surface-2);
            border-radius: var(--radius-md);
            padding: 18px 20px;
            margin-bottom: 14px;
        }
        .custom-section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.6px; color: var(--text-3); margin-bottom: 12px;
        }
        .custom-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .validation-error {
            background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 8px; margin-top: 10px; color: #856404; font-size: 13px;
        }

        @media (max-width: 600px) {
            .custom-grid-2 { grid-template-columns: 1fr; }
            #step2-layout { grid-template-columns: 1fr !important; }
            #livePreviewCol { position: static !important; }
        }
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
                <div id="validationError" class="validation-error" style="display: none;"></div>

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

                <button type="button" onclick="validateStep1() && goToStep(2)">Продовжити</button>
            </div>

            <div id="step-2-content" class="step-section" style="display: none;">

                <div id="step2-layout" style="display: grid; grid-template-columns: 1fr 180px; gap: 20px; align-items: start;">

                    <div>
                        <div class="custom-section">
                            <div class="custom-section-title">Кольори</div>
                            <div class="custom-grid-2">
                                <div class="form-group" style="margin:0">
                                    <label>Колір точок</label>
                                    <div class="color-combo">
                                        <input type="color" id="fgColorPicker" value="#000000">
                                        <input type="text"  id="fgColorHex" name="qr_color" value="#000000" maxlength="7" placeholder="#000000">
                                    </div>
                                </div>
                                <div class="form-group" style="margin:0">
                                    <label>Колір фону</label>
                                    <div class="color-combo">
                                        <input type="color" id="bgColorPicker" value="#ffffff">
                                        <input type="text"  id="bgColorHex" name="bg_color" value="#ffffff" maxlength="7" placeholder="#ffffff">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="custom-section">
                            <div class="custom-section-title">Форма точок даних</div>
                            <div class="style-picker" id="dotStylePicker">

                                <label class="style-option selected" data-val="square">
                                    <input type="radio" name="qr_style" value="square" checked style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><rect x="1" y="1" width="8" height="8" rx="0"/></svg>
                                    Квадрат
                                </label>

                                <label class="style-option" data-val="rounded">
                                    <input type="radio" name="qr_style" value="rounded" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><rect x="1" y="1" width="8" height="8" rx="2.5"/></svg>
                                    Округл.
                                </label>

                                <label class="style-option" data-val="circle">
                                    <input type="radio" name="qr_style" value="circle" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>
                                    Коло
                                </label>

                                <label class="style-option" data-val="diamond">
                                    <input type="radio" name="qr_style" value="diamond" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><polygon points="5,1 9,5 5,9 1,5"/></svg>
                                    Ромб
                                </label>

                                <label class="style-option" data-val="vertical">
                                    <input type="radio" name="qr_style" value="vertical" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><rect x="2.5" y="0" width="5" height="10" rx="1"/></svg>
                                    Вертик.
                                </label>

                                <label class="style-option" data-val="horizontal">
                                    <input type="radio" name="qr_style" value="horizontal" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><rect x="0" y="2.5" width="10" height="5" rx="1"/></svg>
                                    Горизонт.
                                </label>

                            </div>
                        </div>

                        <div class="custom-section">
                            <div class="custom-section-title">Форма рамки очей (зовнішня)</div>
                            <div class="style-picker" id="eyeOuterPicker">

                                <label class="style-option selected" data-val="square">
                                    <input type="radio" name="eye_outer" value="square" checked style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10">
                                        <rect x="1" y="1" width="8" height="8" rx="0" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    </svg>
                                    Квадрат
                                </label>

                                <label class="style-option" data-val="rounded">
                                    <input type="radio" name="eye_outer" value="rounded" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10">
                                        <rect x="1" y="1" width="8" height="8" rx="2.5" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    </svg>
                                    Округл.
                                </label>

                                <label class="style-option" data-val="circle">
                                    <input type="radio" name="eye_outer" value="circle" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10">
                                        <circle cx="5" cy="5" r="4" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    </svg>
                                    Коло
                                </label>

                            </div>
                        </div>

                        <div class="custom-section">
                            <div class="custom-section-title">Форма ядра очей (внутрішня)</div>
                            <div class="style-picker" id="eyeInnerPicker">

                                <label class="style-option selected" data-val="square">
                                    <input type="radio" name="eye_inner" value="square" checked style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><rect x="2.5" y="2.5" width="5" height="5" rx="0"/></svg>
                                    Квадрат
                                </label>

                                <label class="style-option" data-val="rounded">
                                    <input type="radio" name="eye_inner" value="rounded" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><rect x="2.5" y="2.5" width="5" height="5" rx="1.5"/></svg>
                                    Округл.
                                </label>

                                <label class="style-option" data-val="circle">
                                    <input type="radio" name="eye_inner" value="circle" style="display:none">
                                    <svg width="36" height="36" viewBox="0 0 10 10"><circle cx="5" cy="5" r="2.5"/></svg>
                                    Коло
                                </label>

                            </div>
                        </div>

                        <div class="custom-section">
                            <div class="custom-section-title">Додатково</div>
                            <div class="custom-grid-2">
                                <div class="form-group" style="margin:0">
                                    <label>Розмір файлу</label>
                                    <select name="qr_size">
                                        <option value="300">300 × 300 px</option>
                                        <option value="400" selected>400 × 400 px</option>
                                        <option value="600">600 × 600 px</option>
                                        <option value="800">800 × 800 px</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0">
                                    <label>Білий простір (модулів)</label>
                                    <select name="margin">
                                        <option value="0">0 — мінімальний</option>
                                        <option value="1" selected>1 — стандартний</option>
                                        <option value="2">2 — широкий</option>
                                        <option value="4">4 — дуже широкий</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0; grid-column: span 2;">
                                    <label>Логотип у центрі (необов'язково)</label>
                                    <input type="file" name="qr_logo" id="qrLogoInput" accept="image/png,image/jpeg,image/svg+xml">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div id="livePreviewCol" style="position: sticky; top: 100px; text-align: center;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-3); margin-bottom: 10px;">Попередній перегляд</div>
                        <div id="livePreviewBox">
                            <div id="livePreviewSvg">
                                <div style="width:120px;height:120px;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:12px;text-align:center;">
                                    Налаштуйте<br>параметри
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:10px;font-size:11px;color:var(--text-3);">
                            Живий перегляд<br>оновлюється при зміні
                        </div>
                    </div>

                </div>

                <div style="display: flex; gap: 12px; margin-top: 20px;">
                    <button type="button" onclick="goToStep(1)" style="background: var(--surface-2); color: var(--text); width: 35%;">← Назад</button>
                    <button type="submit" style="width: 65%;">Згенерувати QR-код</button>
                </div>

            </div>

            <div id="step-3-content" class="step-section" style="display: none; text-align: center; padding: 20px 0;">

                <div id="qrLoader">
                    <div class="spinner" style="border: 4px solid rgba(0,0,0,0.08); width: 44px; height: 44px; border-radius: 50%; border-left-color: var(--accent); animation: spin 1s linear infinite; margin: 30px auto;"></div>
                    <p style="color: var(--text-2); font-size: 14px;">Рендеринг QR-коду…</p>
                </div>

                <div id="qrResult" style="display: none;">
                    <h3 style="margin: 0 0 20px 0; font-weight: 600;">Готово до використання</h3>

                    <div class="result-preview-box" style="background:#fff; padding:16px; border-radius:18px; display:inline-block; box-shadow: var(--shadow-md); border:1px solid var(--border-solid); margin-bottom:24px;">
                        <div id="generatedQrSvgWrap" style="width:240px;height:240px;display:flex;align-items:center;justify-content:center;overflow:hidden;"></div>
                    </div>

                    <div style="margin-bottom:24px; padding:0 20px;">
                        <p id="resultDisplayLabel" style="margin:0 0 8px; font-size:13px; color:var(--text-2); font-weight:500;">Вміст QR-коду:</p>
                        <div style="display:inline-flex; align-items:center; justify-content:center; background:var(--surface-2); border:1px solid var(--border-solid); padding:10px 16px; border-radius:30px; max-width:90%; box-sizing:border-box;">
                            <span id="resultDisplayIcon" style="margin-right:8px; font-size:14px; flex-shrink:0;">🔗</span>
                            <a id="resultDisplayLink" href="" target="_blank" class="apple-link" style="display:inline-block; padding:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:14px; font-weight:600; text-decoration:none; max-width:220px;"></a>
                        </div>
                    </div>

                    <div class="result-actions-wrapper" style="display:flex; flex-direction:column; gap:10px; max-width:300px; margin:0 auto;">
                        <button type="button" id="downloadSvgBtn" style="background:var(--accent); color:#fff;">
                            Завантажити SVG (вектор)
                        </button>
                        <button type="button" id="downloadPngBtn" style="background:var(--surface-2); color:var(--text); border:1px solid var(--border-solid);">
                            Завантажити PNG (растр)
                        </button>
                        <button type="button" onclick="resetGenerator()" style="background:transparent; color:var(--accent); border:none; cursor:pointer; font-weight:500; font-size:14px; padding:10px; width:auto; margin:0 auto;">
                            + Нова генерація
                        </button>
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
            <div id="pagination-wrapper" style="display: flex; gap: 6px; align-items: center; background: #f5f5f7; padding: 6px; border-radius: 12px;"></div>
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
    const baseAppPath = '/QR-code generator/public/';
    let currentPage = 1;
    let totalItems  = 0;
    const limitPerPage = 5;
    const searchInput       = document.getElementById('history-search');
    const historyContainer  = document.getElementById('history-container');
    const paginationWrapper = document.getElementById('pagination-wrapper');
    let _lastSvg = '';

    function validateStep1() {
        const type = document.getElementById('hiddenTypeInput').value;
        const errorEl = document.getElementById('validationError');
        let error = '';

        if (type === 'url' || type === 'text') {
            const content = document.getElementById('mainContentInput').value.trim();
            if (!content) error = '❌ Будь ласка, заповніть ' + (type === 'url' ? 'посилання' : 'текст');
        } else if (type === 'wifi') {
            const ssid = document.getElementById('wifiSsidInput').value.trim();
            if (!ssid) error = '❌ Будь ласка, введіть назву Wi-Fi мережи';
        } else if (type === 'call') {
            const phone = document.getElementById('callPhoneInput').value.trim();
            if (!phone) error = '❌ Будь ласка, введіть номер телефону';
        } else if (type === 'vcard') {
            const name = document.getElementById('vcardNameInput').value.trim();
            const phone = document.getElementById('vcardPhoneInput').value.trim();
            if (!name || !phone) error = '❌ Будь ласка, заповніть ім\'я та телефон';
        } else if (['image', 'video', 'pdf'].includes(type)) {
            const file = document.getElementById('mainFileInput').files[0];
            if (!file) error = '❌ Будь ласка, оберіть файл';
            else if (file.size > 10 * 1024 * 1024) error = '❌ Файл занадто великий (максимум 10 МБ)';
        }

        if (error) {
            errorEl.textContent = error;
            errorEl.style.display = 'block';
            window.scrollTo({top: document.getElementById('validationError').offsetTop - 100, behavior: 'smooth'});
            return false;
        }

        errorEl.style.display = 'none';
        return true;
    }

    function goToStep(stepNum) {
        document.querySelectorAll('.step-section').forEach(el => el.style.display = 'none');
        document.getElementById('step-' + stepNum + '-content').style.display = 'block';
        document.querySelectorAll('.step-indicator').forEach((el, i) => {
            if (i + 1 === stepNum) el.classList.add('active');
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
        if (defaultCard) {
            defaultCard.style.border = '2px solid #0071e3';
            defaultCard.style.background = 'rgba(0,113,227,0.04)';
        }
        document.getElementById('hiddenTypeInput').value = 'url';
        handleTypeChange('url');
        document.querySelectorAll('.style-picker').forEach(picker => {
            picker.querySelectorAll('.style-option').forEach(opt => opt.classList.remove('selected'));
            const first = picker.querySelector('.style-option');
            if (first) first.classList.add('selected');
        });
        document.getElementById('fgColorPicker').value = '#000000';
        document.getElementById('fgColorHex').value    = '#000000';
        document.getElementById('bgColorPicker').value = '#ffffff';
        document.getElementById('bgColorHex').value    = '#ffffff';
        document.getElementById('qrLoader').style.display = 'block';
        document.getElementById('qrResult').style.display  = 'none';
        _lastSvg = '';
        buildLivePreview();
        goToStep(1);
    }

    document.querySelectorAll('.content-type-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.content-type-card').forEach(c => {
                c.style.border     = '1px solid #d2d2d7';
                c.style.background = 'transparent';
            });
            this.style.border     = '2px solid #0071e3';
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
        const label    = document.getElementById('inputLabel');
        const fileLbl  = document.getElementById('fileLabel');

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
            fileDiv.style.display = 'block'; fileLbl.innerText = 'Оберіть зображення (PNG/JPG)';
            document.getElementById('mainFileInput').accept = 'image/*';
        } else if (type === 'video') {
            fileDiv.style.display = 'block'; fileLbl.innerText = 'Оберіть відеоролик (MP4)';
            document.getElementById('mainFileInput').accept = 'video/*';
        } else if (type === 'pdf') {
            fileDiv.style.display = 'block'; fileLbl.innerText = 'Оберіть файл (PDF)';
            document.getElementById('mainFileInput').accept = '.pdf,application/pdf';
        }
    }

    function initStylePicker(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.querySelectorAll('.style-option').forEach(opt => {
            opt.addEventListener('click', () => {
                container.querySelectorAll('.style-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                scheduleLivePreview();
            });
        });
    }
    initStylePicker('dotStylePicker');
    initStylePicker('eyeOuterPicker');
    initStylePicker('eyeInnerPicker');
    document.querySelectorAll('select[name="qr_size"], select[name="margin"]').forEach(el => {
        el.addEventListener('change', scheduleLivePreview);
    });

    function initColorSync(pickerId, hexId) {
        const picker = document.getElementById(pickerId);
        const hex    = document.getElementById(hexId);
        if (!picker || !hex) return;
        picker.addEventListener('input', () => { hex.value = picker.value; scheduleLivePreview(); });
        hex.addEventListener('input', () => {
            const v = hex.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(v)) picker.value = v;
            scheduleLivePreview();
        });
    }
    initColorSync('fgColorPicker', 'fgColorHex');
    initColorSync('bgColorPicker', 'bgColorHex');

    let _previewTimer = null;
    function scheduleLivePreview() {
        clearTimeout(_previewTimer);
        _previewTimer = setTimeout(buildLivePreview, 200);
    }

    function buildLivePreview() {
        const fgColor  = (document.getElementById('fgColorHex') || {}).value || '#000000';
        const bgColor  = (document.getElementById('bgColorHex') || {}).value || '#ffffff';
        const dotStyle = (document.querySelector('input[name="qr_style"]:checked')  || {}).value || 'square';
        const eyeOuter = (document.querySelector('input[name="eye_outer"]:checked') || {}).value || 'square';
        const eyeInner = (document.querySelector('input[name="eye_inner"]:checked') || {}).value || 'square';

        const n    = 21;
        const size = 120;
        const cs   = size / (n + 2);
        const off  = cs;

        const matrix = makeFakeMatrix(n);
        const eps    = [[0,0],[0,n-7],[n-7,0]];

        let shapes = '';
        for (let row = 0; row < n; row++) {
            for (let col = 0; col < n; col++) {
                if (!matrix[row][col]) continue;
                const x = off + col * cs;
                const y = off + row * cs;
                const role = getEyeRole(row, col, n, eps);

                if (role === 'outer') {
                    if (isEyeOrigin(row, col, eps))  shapes += svgEyeOuter(x, y, cs, eyeOuter, fgColor);
                    continue;
                }
                if (role === 'inner') {
                    if (isInnerOrigin(row, col, eps)) shapes += svgEyeInner(x, y, cs, eyeInner, fgColor);
                    continue;
                }
                shapes += svgDot(x, y, cs, dotStyle, fgColor);
            }
        }

        const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 ' + size + ' ' + size + '">'
            + '<rect width="' + size + '" height="' + size + '" fill="' + bgColor + '"/>'
            + shapes + '</svg>';

        const previewEl = document.getElementById('livePreviewSvg');
        if (previewEl) previewEl.innerHTML = svg;
        const boxEl = document.getElementById('livePreviewBox');
        if (boxEl) boxEl.style.background = bgColor;
    }

    function makeFakeMatrix(n) {
        const m = Array.from({length: n}, () => Array(n).fill(false));
        [[0,0],[0,n-7],[n-7,0]].forEach(function(pos) {
            var r0 = pos[0], c0 = pos[1];
            for (var r = r0; r < r0+7; r++) {
                for (var c = c0; c < c0+7; c++) {
                    m[r][c] = (r===r0||r===r0+6||c===c0||c===c0+6) || (r>=r0+2&&r<=r0+4&&c>=c0+2&&c<=c0+4);
                }
            }
        });
        for (var i = 8; i < n-8; i++) { m[6][i] = (i%2===0); m[i][6] = (i%2===0); }
        for (var r = 0; r < n; r++) {
            for (var c = 0; c < n; c++) {
                if (!m[r][c] && c!==6 && r!==6) m[r][c] = ((r*3+c*7+r*c)%5) < 2;
            }
        }
        return m;
    }

    function getEyeRole(row, col, n, eps) {
        for (var i = 0; i < eps.length; i++) {
            var er = eps[i][0], ec = eps[i][1];
            if (row>=er && row<er+7 && col>=ec && col<ec+7) {
                if (row>=er+2 && row<er+5 && col>=ec+2 && col<ec+5) return 'inner';
                return 'outer';
            }
        }
        return 'data';
    }
    function isEyeOrigin(row, col, eps) {
        return eps.some(function(p) { return row===p[0] && col===p[1]; });
    }
    function isInnerOrigin(row, col, eps) {
        return eps.some(function(p) { return row===p[0]+2 && col===p[1]+2; });
    }

    function svgDot(x, y, cs, style, color) {
        var pad = cs*0.08, x2=x+pad, y2=y+pad, s=cs-pad*2, cx=x+cs/2, cy=y+cs/2, r=s/2;
        if (style==='circle')   return '<circle cx="'+cx+'" cy="'+cy+'" r="'+r+'" fill="'+color+'"/>';
        if (style==='rounded')  { var rr=s*0.35; return '<rect x="'+x2+'" y="'+y2+'" width="'+s+'" height="'+s+'" rx="'+rr+'" fill="'+color+'"/>'; }
        if (style==='diamond')  { var pts=cx+','+(cy-r)+' '+(cx+r)+','+cy+' '+cx+','+(cy+r)+' '+(cx-r)+','+cy; return '<polygon points="'+pts+'" fill="'+color+'"/>'; }
        if (style==='vertical')   { var bw=cs*0.76; return '<rect x="'+(x+(cs-bw)/2)+'" y="'+y+'" width="'+bw+'" height="'+cs+'" fill="'+color+'"/>'; }
        if (style==='horizontal') { var bh=cs*0.76; return '<rect x="'+x+'" y="'+(y+(cs-bh)/2)+'" width="'+cs+'" height="'+bh+'" fill="'+color+'"/>'; }
        return '<rect x="'+x2+'" y="'+y2+'" width="'+s+'" height="'+s+'" fill="'+color+'"/>';
    }
    function svgEyeOuter(x, y, cs, style, color) {
        var size=cs*7, sw=cs, r=size/2, cx=x+r, cy=y+r, x2=x+sw/2, y2=y+sw/2, s2=size-sw;
        if (style==='circle')  return '<circle cx="'+cx+'" cy="'+cy+'" r="'+(r-sw/2)+'" stroke="'+color+'" stroke-width="'+sw+'" fill="none"/>';
        if (style==='rounded') { var rr=size*0.22; return '<rect x="'+x2+'" y="'+y2+'" width="'+s2+'" height="'+s2+'" rx="'+rr+'" stroke="'+color+'" stroke-width="'+sw+'" fill="none"/>'; }
        return '<rect x="'+x2+'" y="'+y2+'" width="'+s2+'" height="'+s2+'" stroke="'+color+'" stroke-width="'+sw+'" fill="none"/>';
    }
    function svgEyeInner(x, y, cs, style, color) {
        var size=cs*3, cx=x+size/2, cy=y+size/2;
        if (style==='circle')  return '<circle cx="'+cx+'" cy="'+cy+'" r="'+(size/2)+'" fill="'+color+'"/>';
        if (style==='rounded') { var rr=size*0.28; return '<rect x="'+x+'" y="'+y+'" width="'+size+'" height="'+size+'" rx="'+rr+'" fill="'+color+'"/>'; }
        return '<rect x="'+x+'" y="'+y+'" width="'+size+'" height="'+size+'" fill="'+color+'"/>';
    }

    buildLivePreview();

    document.getElementById('ajaxQrForm').addEventListener('submit', function(e) {
        e.preventDefault();
        goToStep(3);

        const formData = new FormData(this);

        fetch(baseAppPath + 'generate', { method: 'POST', body: formData })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(data) {
                if (data && data.media_path) {
                    document.getElementById('qrLoader').style.display = 'none';
                    document.getElementById('qrResult').style.display = 'block';

                    var svgWrap = document.getElementById('generatedQrSvgWrap');
                    if (data.svg) {
                        _lastSvg = data.svg;
                        svgWrap.innerHTML = data.svg;
                        var svgEl = svgWrap.querySelector('svg');
                        if (svgEl) { svgEl.style.width = '240px'; svgEl.style.height = '240px'; }
                    }

                    document.getElementById('downloadSvgBtn').onclick = function() {
                        if (!_lastSvg) { alert('SVG недоступний'); return; }
                        var blob = new Blob([_lastSvg], {type: 'image/svg+xml'});
                        var url  = URL.createObjectURL(blob);
                        var a    = document.createElement('a');
                        a.href = url; a.download = 'qr-code.svg'; a.click();
                        setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
                    };


                    var type = document.getElementById('hiddenTypeInput').value;
                    var displayLink  = document.getElementById('resultDisplayLink');
                    var displayLabel = document.getElementById('resultDisplayLabel');
                    var displayIcon  = document.getElementById('resultDisplayIcon');

                    var targetUrl = '';
                    if (type==='url'||type==='text') targetUrl = (document.getElementById('mainContentInput')||{}).value||'';
                    else if (type==='wifi')  targetUrl = 'SSID: '+((document.getElementById('wifiSsidInput')||{}).value||'');
                    else if (type==='call')  targetUrl = (document.getElementById('callPhoneInput')||{}).value||'';
                    else if (type==='vcard') targetUrl = ((document.getElementById('vcardNameInput')||{}).value||'')+' / '+((document.getElementById('vcardPhoneInput')||{}).value||'');

                    var iconMap = {url:'🔗',text:'📝',wifi:'📶',call:'📞',vcard:'👤',image:'🖼️',video:'🎥',pdf:'📄'};
                    displayIcon.textContent = iconMap[type] || '🔗';
                    displayLink.textContent = targetUrl;
                    displayLink.href        = targetUrl.indexOf('http')===0 ? targetUrl : '#';
                    displayLink.style.color = targetUrl.indexOf('http')===0 ? 'var(--accent)' : 'var(--text)';
                    displayLink.style.pointerEvents = targetUrl.indexOf('http')===0 ? 'auto' : 'none';

                    loadHistory(1, '');

                } else {
                    alert('Не вдалося згенерувати: ' + (data.message || 'Невідома помилка'));
                    goToStep(2);
                }
            })
            .catch(function(err) {
                console.error(err);
                alert('Помилка з\'єднання з сервером.');
                goToStep(2);
            });
    });

    document.getElementById('downloadPngBtn').onclick = function(e) {
        e.preventDefault();

        const svgElement = document.querySelector('#generatedQrSvgWrap svg');
        if (!svgElement) { alert('SVG не знайдено'); return; }

        const xml = new XMLSerializer().serializeToString(svgElement);
        const blob = new Blob([xml], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(blob);

        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');

            const exportSize = 1000;
            canvas.width = exportSize;
            canvas.height = exportSize;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, exportSize, exportSize);
            ctx.drawImage(img, 0, 0, exportSize, exportSize);

            const png = canvas.toDataURL('image/png');

            const a = document.createElement('a');
            a.href = png;
            a.download = 'qr-code.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            URL.revokeObjectURL(url);
        };
        img.src = url;
    };

    function loadHistory(page, search) {
        page   = page   || 1;
        search = search || '';
        fetch(baseAppPath + 'get-history-ajax?page=' + page + '&search=' + encodeURIComponent(search))
            .then(function(res) {
                if (!res.ok) throw new Error('Помилка завантаження');
                return res.json();
            })
            .then(function(data) {
                if (!Array.isArray(data)) {
                    throw new Error('Невірний формат відповіді');
                }

                var qrs = data;
                totalItems = qrs.length < limitPerPage ? (page-1)*limitPerPage+qrs.length : page*limitPerPage+1;

                if (qrs.length === 0) {
                    historyContainer.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;color:#86868b;">Нічого не знайдено</td></tr>';
                    paginationWrapper.innerHTML = '';
                    return;
                }

                historyContainer.innerHTML = qrs.map(function(qr) {
                    var displayTitle = qr.title || '';
                    var displaySub   = '';
                    var icon         = '🔗';
                    var targetUrl    = qr.original_url;
                    var shortLink    = qr.short_code ? (window.location.protocol + '//' + window.location.host + baseAppPath + 'r/' + qr.short_code) : '';

                    if (qr.qr_type==='url')        { displayTitle=displayTitle||qr.original_url; displaySub=qr.original_url; }
                    else if (qr.qr_type==='text')  { displayTitle=displayTitle||(qr.original_url.substring(0,40)+'...'); displaySub='Текстові дані'; icon='📝'; }
                    else if (qr.qr_type==='wifi')  { displayTitle=displayTitle||'Wi-Fi Мережа'; var m=qr.original_url.match(/S:(.*?);/); displaySub='SSID: '+(m?m[1]:'?'); icon='📶'; }
                    else if (qr.qr_type==='call')  { displayTitle=displayTitle||'Дзвінок'; displaySub=qr.original_url; icon='📞'; }
                    else if (qr.qr_type==='vcard') { displayTitle=displayTitle||'Контакт vCard'; var fm=qr.original_url.match(/FN:(.*)/); displaySub=fm?fm[1].trim():'vCard'; icon='👤'; }
                    else if (['image','video','pdf'].indexOf(qr.qr_type)>=0) {
                        displayTitle=displayTitle||'Медіафайл ('+qr.qr_type.toUpperCase()+')';
                        displaySub=shortLink||'Медіафайл';
                        if(qr.qr_type==='image')icon='🖼️'; if(qr.qr_type==='video')icon='🎥'; if(qr.qr_type==='pdf')icon='📄';
                        targetUrl=shortLink||qr.original_url;
                    }

                    var dateStr = new Date(qr.created_at).toLocaleDateString('uk-UA',{day:'2-digit',month:'2-digit',year:'numeric'});

                    return '<tr id="qr-row-'+qr.id+'" style="border-bottom:1px solid #e8e8ed;">'
                        +'<td style="padding:12px;"><input type="checkbox" class="qr-checkbox" value="'+qr.id+'"></td>'
                        +'<td style="padding:12px;"><span class="badge" style="background:#e8e8ed;color:#1d1d1f;font-size:10px;font-weight:700;padding:4px 8px;border-radius:6px;">'+qr.qr_type.toUpperCase()+'</span></td>'
                        +'<td style="padding:12px;">'
                        +'<div style="display:flex;align-items:center;gap:15px;">'
                        +'<div style="width:46px;height:46px;background:#fff;border-radius:10px;padding:3px;border:1px solid #d2d2d7;box-shadow:0 2px 5px rgba(0,0,0,.04);flex-shrink:0;position:relative;">'
                        +'<img src="'+baseAppPath+qr.media_path+'" style="width:100%;height:100%;object-fit:contain;border-radius:6px;">'
                        +'<div style="position:absolute;bottom:-4px;right:-4px;background:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:10px;border:1px solid #d2d2d7;">'+icon+'</div>'
                        +'</div>'
                        +'<div style="max-width:250px;overflow:hidden;">'
                        +'<div style="font-weight:600;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+displayTitle+'</div>'
                        +'<div style="font-size:12px;color:#0071e3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+displaySub+'</div>'
                        +'</div></div></td>'
                        +'<td style="padding:12px;color:#86868b;font-size:13px;">'+dateStr+'</td>'
                        +'<td style="padding:12px;text-align:right;">'
                        +'<div style="display:flex;gap:8px;justify-content:flex-end;align-items:center;">'
                        +'<button class="view-qr-btn" data-path="'+qr.media_path+'" data-content="'+targetUrl+'" data-type="'+qr.qr_type+'" style="padding:8px 14px;font-size:12px;background:rgba(0,113,227,.1);color:#0071e3;border:none;border-radius:12px;cursor:pointer;font-weight:500;width:auto;margin-top:0;">Відкрити</button>'
                        +'<button class="delete-qr-btn" data-id="'+qr.id+'" style="background:none;color:#ff3b30;border:none;cursor:pointer;font-size:20px;padding:0 5px;width:auto;margin-top:0;">&times;</button>'
                        +'</div></td></tr>';
                }).join('');

                var lc = document.getElementById('qr-list-container');
                if (lc) { lc.classList.remove('fade-in'); void lc.offsetWidth; lc.classList.add('fade-in'); }

                currentPage = page;
                renderPagination(qrs.length);
                var sa = document.getElementById('selectAll');
                if (sa) sa.checked = false;
                updateBulkButton();
            })
            .catch(function(err) {
                console.error(err);
                historyContainer.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:#ff3b30;">Помилка завантаження даних</td></tr>';
            });
    }

    function renderPagination(rowCount) {
        var totalPages = Math.ceil(totalItems / limitPerPage) || 1;
        var html = '';
        html += '<button title="На початок" class="pagination-btn" '+(currentPage===1?'disabled':'')+' onclick="changePage(1)">«</button>';
        html += '<button title="Назад" class="pagination-btn" '+(currentPage===1?'disabled':'')+' onclick="changePage('+(currentPage-1)+')">‹</button>';
        var start = Math.max(1, currentPage-1);
        var end   = Math.min(totalPages, start+2);
        if (end-start < 2) start = Math.max(1, end-2);
        for (var i=start; i<=end; i++) {
            if (i===currentPage) html += '<button class="pagination-btn active" disabled>'+i+'</button>';
            else html += '<button class="pagination-btn" onclick="changePage('+i+')">'+i+'</button>';
        }
        var last = currentPage>=totalPages || rowCount<limitPerPage;
        html += '<button title="Вперед" class="pagination-btn" '+(last?'disabled':'')+' onclick="changePage('+(currentPage+1)+')">›</button>';
        html += '<button title="В кінець" class="pagination-btn" '+(last?'disabled':'')+' onclick="changePage('+totalPages+')">»</button>';
        paginationWrapper.innerHTML = html;
    }

    window.changePage = function(page) { loadHistory(page, searchInput.value); };

    var _typingTimer;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(_typingTimer);
        _typingTimer = setTimeout(function() { loadHistory(1, e.target.value); }, 400);
    });

    loadHistory(1, '');

    function updateBulkButton() {
        var sel    = document.querySelectorAll('.qr-checkbox:checked').length;
        var btn    = document.getElementById('delete-selected');
        var span   = document.getElementById('selected-count');
        if (btn) { btn.style.display = sel>0 ? 'block' : 'none'; span.innerText = sel; }
    }

    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectAll') {
            document.querySelectorAll('.qr-checkbox').forEach(function(cb) { cb.checked = e.target.checked; });
            updateBulkButton();
        } else if (e.target.classList.contains('qr-checkbox')) {
            updateBulkButton();
        }
    });

    function sendDeleteRequest(ids) {
        if (!confirm('Видалити обрані об\'єкти (' + ids.length + ' шт.)?')) return;
        fetch(baseAppPath + 'delete-qrs', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ids: ids})
        })
            .then(function(r) { return r.json(); })
            .then(function() { loadHistory(currentPage, searchInput.value); });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-qr-btn')) {
            sendDeleteRequest([e.target.getAttribute('data-id')]);
        }
        if (e.target.id === 'delete-selected') {
            var ids = Array.from(document.querySelectorAll('.qr-checkbox:checked')).map(function(cb) { return cb.value; });
            sendDeleteRequest(ids);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var modal           = document.getElementById('qrModal');
        var modalImg        = document.getElementById('modalImg');
        var modalContentLink= document.getElementById('modalContentLink');
        var modalDownload   = document.getElementById('modalDownload');

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('view-qr-btn')) {
                var rawPath    = e.target.getAttribute('data-path');
                var contentUrl = e.target.getAttribute('data-content');
                var qrType     = e.target.getAttribute('data-type');

                if (rawPath && rawPath.endsWith('.png')) {
                    rawPath = rawPath.replace('.png', '.svg');
                }

                var fullPath   = baseAppPath + rawPath;
                modalImg.src = fullPath;
                modalContentLink.innerText = contentUrl;

                var iconMap = {image:'🖼️',video:'🎥',pdf:'📄',text:'📝',wifi:'📶',call:'📞',vcard:'👤'};
                var dynIcon = document.getElementById('modalDynamicIcon');
                if (dynIcon) dynIcon.innerText = iconMap[qrType] || '🔗';

                if (contentUrl.indexOf('http://')===0 || contentUrl.indexOf('https://')===0) {
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

// Для SVG залишаємо оригінальний fullPath
                var modalDownloadSvgBtn = document.getElementById('modalDownloadSvg');
                if (modalDownloadSvgBtn) {
                    modalDownloadSvgBtn.href = fullPath;
                }
                modal.style.display = 'flex';
            }
        });

        document.getElementById('closeModal').onclick = function() { modal.style.display = 'none'; };
        window.onclick = function(e) { if (e.target === modal) modal.style.display = 'none'; };
    });
</script>
</body>
</html>