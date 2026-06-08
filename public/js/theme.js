(function () {
    'use strict';

    const STORAGE_KEY = 'gqr_theme';
    const THEMES = ['light', 'dark', 'night'];

    const THEME_META = {
        light: { icon: '☀️', label: 'Світла' },
        dark:  { icon: '🌙', label: 'Темна'  },
        night: { icon: '🌌', label: 'Нічна'  },
    };

    function getTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
        updateToggleUI(theme);
    }

    function nextTheme(current) {
        const idx = THEMES.indexOf(current);
        return THEMES[(idx + 1) % THEMES.length];
    }

    applyTheme(getTheme());

    function buildToggle() {
        if (document.getElementById('theme-toggle')) return;

        const btn = document.createElement('button');
        btn.id = 'theme-toggle';
        btn.setAttribute('aria-label', 'Змінити тему');
        btn.setAttribute('title', 'Змінити тему');
        btn.innerHTML = `<span class="theme-icon"></span><span class="theme-label"></span>`;

        btn.addEventListener('click', function () {
            const current = getTheme();
            const next    = nextTheme(current);
            applyTheme(next);

            /* Small bounce animation */
            btn.style.transform = 'scale(0.88) translateY(0)';
            setTimeout(() => { btn.style.transform = ''; }, 180);
        });

        document.body.appendChild(btn);
        updateToggleUI(getTheme());
    }

    function updateToggleUI(theme) {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;
        const meta = THEME_META[theme] || THEME_META.light;
        btn.querySelector('.theme-icon').textContent  = meta.icon;
        btn.querySelector('.theme-label').textContent = meta.label;
        btn.setAttribute('title', 'Тема: ' + meta.label + ' → натисни для зміни');
    }

    function enhanceFileInputs() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            /* Already enhanced? */
            if (input.dataset.enhanced) return;
            input.dataset.enhanced = '1';

            input.addEventListener('change', function () {
                const files = this.files;
                if (!files || files.length === 0) return;

                const name = files.length === 1
                    ? files[0].name
                    : files.length + ' файли обрано';

                this.setAttribute('data-filename', name);

                const label = this.previousElementSibling;
                if (label && label.tagName === 'LABEL') {
                    const orig = label.dataset.origText || label.textContent;
                    label.dataset.origText = orig;
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    const observer = new MutationObserver(function (mutations) {
        for (const m of mutations) {
            if (m.addedNodes.length) {
                enhanceFileInputs();
                break;
            }
        }
    });

    function init() {
        buildToggle();
        enhanceFileInputs();
        buildScrollTop();

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    function buildScrollTop() {
        if (document.getElementById('scroll-top-btn')) return;
        var btn = document.createElement('button');
        btn.id = 'scroll-top-btn';
        btn.setAttribute('aria-label', 'Вгору');
        btn.setAttribute('title', 'Вгору');
        btn.innerHTML = '↑';
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        document.body.appendChild(btn);

        window.addEventListener('scroll', function () {
            if (window.scrollY > 320) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }, { passive: true });
    }

    function downloadPngFromModalImg() {
        const modalImg = document.getElementById('modalImg');
        if (!modalImg || !modalImg.src) return;
        const svgSrc = modalImg.src.replace(/\.png(\?.*)?$/, '.svg');

        fetch(svgSrc)
            .then(function (r) { return r.text(); })
            .then(function (svgText) {
                const blob = new Blob([svgText], { type: 'image/svg+xml;charset=utf-8' });
                const url  = URL.createObjectURL(blob);
                const img  = new Image();
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    const exportSize = 1000;
                    canvas.width  = exportSize;
                    canvas.height = exportSize;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, exportSize, exportSize);
                    ctx.drawImage(img, 0, 0, exportSize, exportSize);
                    const pngUrl = canvas.toDataURL('image/png');
                    URL.revokeObjectURL(url);
                    const a = document.createElement('a');
                    a.href     = pngUrl;
                    a.download = 'qrcode.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                };
                img.src = url;
            });
    }

    document.addEventListener('click', function (e) {
        if (e.target && (e.target.id === 'modalDownload' || e.target.id === 'modalDownloadPng')) {
            e.preventDefault();
            downloadPngFromModalImg();
        }
    });

})();