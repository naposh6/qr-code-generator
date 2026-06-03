# 🚀 GenerQR — Генератор динамічних QR-кодів

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**GenerQR** — веб-застосунок для створення кастомізованих QR-кодів з підтримкою різних типів вмісту, стилізацією зовнішнього вигляду, системою авторизації, профілем користувача та адміністративною панеллю. Реалізований на чистому PHP 8.1+ без використання важких фреймворків.

---

## 🌟 Основні можливості

- **8 типів QR-кодів:** URL, текст, Wi-Fi, зображення, відео, PDF, дзвінок (tel:), vCard
- **Глибока кастомізація:** колір точок і фону, форма модулів (6 варіантів), форма зовнішньої та внутрішньої частини «очей» (3 варіанти кожна), відступи, розмір файлу
- **Логотип у центрі:** можливість накласти власне зображення у центр QR-коду з автоматичною білою підкладкою
- **Живий попередній перегляд:** SVG-перегляд оновлюється при зміні параметрів без перезавантаження сторінки
- **Завантаження PNG та SVG:** збереження як растрового (1000×1000 px), так і векторного файлу
- **Система коротких посилань:** кожному QR-коду присвоюється унікальний `short_code` для перенаправлення
- **Облік сканувань:** при переході за коротким посиланням фіксуються IP-адреса, User-Agent і час
- **Завантаження медіафайлів:** зображення (JPG/PNG), відео (MP4/MOV), PDF до 10 МБ
- **Система авторизації:** реєстрація, вхід, вихід, хешування паролів через `password_hash()`
- **Профіль користувача:** зміна нікнейму, паролю (AJAX), аватару, перегляд та видалення своїх QR-кодів
- **Адміністративна панель:** управління користувачами, зміна ролей, видалення QR-кодів
- **Три теми оформлення:** light, dark, night — з автоматичним збереженням у `localStorage`
- **Адаптивний інтерфейс:** підтримка мобільних пристроїв, планшетів і широких екранів
- **Пагінація та пошук:** в історії генерацій з AJAX-підвантаженням

---

## 🛠️ Технічний стек

| Рівень       | Технологія                                      |
|--------------|-------------------------------------------------|
| Backend      | PHP 8.1+                                        |
| Графіка      | GD Library, Imagick (за наявності)              |
| QR-рушій     | `endroid/qr-code` v5.x                         |
| Змінні середовища | `vlucas/phpdotenv`                         |
| База даних   | MySQL (PDO)                                     |
| Frontend     | HTML5, CSS3, Vanilla JavaScript                 |
| Автозавантаження | Composer PSR-4 + власний `Autoloader`      |

---

## 🏗️ Архітектура проєкту

Проєкт побудований за шаблоном **MVC** з чітким розподілом відповідальностей:

```
HTTP-запит → index.php (Front Controller) → Router → Controller → Service/Repository → View
```

### Рівні застосунку

- **Front Controller** (`public/index.php`) — єдина точка входу, ініціалізація середовища та маршрутизація
- **Router** (`src/Core/Router.php`) — зіставлення URI з обробниками, обробка статичних файлів
- **Controllers** — обробка HTTP-запитів, виклик сервісів і репозиторіїв
- **Services** — бізнес-логіка (генерація QR, завантаження файлів, видалення)
- **Repositories** — взаємодія з базою даних через PDO
- **Models** — реалізують `QrContentInterface`, формують вміст QR
- **Views** — PHP-шаблони з мінімальною логікою
- **Factories** — динамічне створення об'єктів вмісту

---

## 🧩 Патерни проєктування

| Патерн | Де використовується |
|---|---|
| **Singleton** | `Database` — єдине підключення до БД на весь запит |
| **Factory Method** | `QrContentFactory` — вибір класу вмісту за типом (`url`, `wifi`, `vcard` тощо) |
| **Repository** | `QrRepository`, `UserRepository` — ізоляція SQL від бізнес-логіки |
| **Front Controller** | `index.php` + `Router` — єдина точка входу для всіх запитів |
| **Strategy** | `QrGeneratorService` — гнучка зміна стилю рендерингу (форма точок і очей) через параметри |
| **Interface / Contract** | `QrContentInterface` — контракт для всіх типів вмісту QR |

---

## 📐 Принципи розробки

- **Single Responsibility** — кожен клас відповідає за одну задачу (генерація, завантаження, видалення)
- **Dependency Injection** — сервіси та репозиторії отримуються через конструктор
- **DRY** — логіка видалення файлів централізована в `QrDeletionService`
- **Type Hinting** — суворе використання типів PHP 8.1
- **Separation of Concerns** — чіткий поділ на Controller / Service / Repository / View

---

## 📁 Структура директорій

```
project/
├── public/                    # Публічна директорія (document root)
│   ├── index.php              # Front Controller — єдина точка входу
│   ├── css/
│   │   └── style.css          # Головний CSS з підтримкою тем
│   ├── js/
│   │   └── theme.js           # Перемикач тем, покращення файлових інпутів
│   ├── assets/                # Іконки та логотипи
│   └── uploads/               # Завантажені файли
│       ├── qr/                # PNG та SVG QR-кодів
│       ├── images/            # Завантажені зображення
│       ├── videos/            # Завантажені відео
│       └── pdfs/              # Завантажені PDF
├── src/
│   ├── Core/
│   │   ├── Autoloader.php     # PSR-4-сумісний автозавантажувач
│   │   ├── Database.php       # Singleton PDO-підключення
│   │   └── Router.php         # Маршрутизатор
│   ├── Controllers/
│   │   ├── AuthController.php     # Реєстрація, вхід, вихід
│   │   ├── UserController.php     # Профіль, QR, пароль, аватар
│   │   ├── AdminController.php    # Адмін-панель
│   │   └── RedirectController.php # Обробка коротких посилань
│   ├── Services/
│   │   ├── QrGeneratorService.php # Генерація SVG/PNG QR-коду
│   │   ├── FileService.php        # Завантаження та видалення файлів
│   │   └── QrDeletionService.php  # Видалення QR та пов'язаних файлів
│   ├── Repositories/
│   │   ├── QrRepository.php       # SQL-операції з QR-кодами
│   │   └── UserRepository.php     # SQL-операції з користувачами
│   ├── Models/
│   │   ├── UrlContent.php
│   │   ├── TextContent.php
│   │   ├── WifiContent.php
│   │   ├── CallContent.php
│   │   ├── VcardContent.php
│   │   └── FileContent.php
│   ├── Factories/
│   │   └── QrContentFactory.php   # Factory для типів вмісту
│   └── Contracts/
│       └── QrContentInterface.php # Інтерфейс вмісту QR
├── views/
│   ├── home.php               # Головна: генератор + історія
│   ├── login.php              # Сторінка входу
│   ├── register.php           # Сторінка реєстрації
│   ├── generate.php           # Обробник генерації (JSON-відповідь)
│   ├── user/
│   │   └── profile.php        # Профіль користувача
│   └── admin/
│       ├── dashboard.php      # Адмін-панель
│       ├── _users_table.php   # AJAX-фрагмент: таблиця користувачів
│       └── _qr_table.php      # AJAX-фрагмент: таблиця QR-кодів
├── vendor/                    # Composer-залежності
├── composer.json
└── .env                       # Змінні середовища (не в репозиторії)
```

---

## ⚙️ Встановлення

### 1. Клонування репозиторію

```bash
git clone https://github.com/naposh6/qr-code-generator.git
cd qr-code-generator
```

### 2. Встановлення залежностей

```bash
composer install
```

> **Важливо:** переконайтеся, що у `php.ini` увімкнені розширення `extension=gd` (обов'язково) та `extension=imagick` (опційно, покращує якість PNG).

### 3. Права доступу

```bash
chmod -R 777 public/uploads/
```

---

## 🔧 Налаштування .env

Створіть файл `.env` у кореневій директорії проєкту:

```env
DB_HOST=localhost
DB_NAME=qr_project_db
DB_USER=root
DB_PASS=your_password
DB_CHARSET=utf8mb4
```

Якщо файл `.env` відсутній, застосунок використає значення за замовчуванням (localhost, root, без пароля).

---

## 🗄️ Структура бази даних

Створіть базу даних і виконайте наступні SQL-запити:

```sql
CREATE DATABASE qr_project_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE qr_project_db;

CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    nickname    VARCHAR(100) DEFAULT NULL,
    role        ENUM('user', 'admin') DEFAULT 'user',
    avatar_path VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE qr_codes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    original_url TEXT NOT NULL,
    short_code   VARCHAR(16) NOT NULL UNIQUE,
    media_path   VARCHAR(255) DEFAULT NULL,
    qr_type      VARCHAR(50) NOT NULL,
    user_id      INT DEFAULT NULL,
    title        VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE scans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    qr_code_id  INT NOT NULL,
    ip_address  VARCHAR(45),
    user_agent  TEXT,
    scanned_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE
);
```

---

## 🚀 Запуск проєкту

### Apache (рекомендовано)

Встановіть `DocumentRoot` на папку `public/` або налаштуйте Virtual Host:

```apache
<VirtualHost *:80>
    ServerName generqr.local
    DocumentRoot /path/to/project/public
    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Переконайтеся, що `mod_rewrite` увімкнено.

### PHP Built-in Server (для розробки)

```bash
cd public
php -S localhost:8000
```

---

## 👥 Ролі користувачів

| Роль    | Можливості |
|---------|-----------|
| `user`  | Реєстрація, вхід, генерація QR-кодів, перегляд своєї історії, видалення своїх QR, редагування профілю (нікнейм, пароль, аватар) |
| `admin` | Усе що `user` + доступ до адмін-панелі: перегляд усіх QR-кодів і користувачів, зміна ролей, видалення будь-яких QR-кодів і користувачів (крім інших адміністраторів) |

Роль встановлюється адміністратором через адмін-панель. Перший адміністратор призначається вручну через базу даних.

---

## 🔐 Система авторизації

- Реєстрація з перевіркою унікальності email
- Вхід з верифікацією через `password_verify()`
- Паролі зберігаються у вигляді bcrypt-хешу (`PASSWORD_BCRYPT`)
- Сесії: `user_id`, `role`, `user_email`, `user_nickname`
- Захист маршрутів через перевірку сесії в конструкторах контролерів
- Зміна паролю доступна через AJAX-запит з профілю

---

## 📱 Маршрути застосунку

| Метод | URI | Опис |
|-------|-----|------|
| GET/POST | `/` | Головна сторінка (генератор + історія) |
| GET/POST | `/login` | Вхід |
| GET/POST | `/register` | Реєстрація |
| GET | `/logout` | Вихід |
| GET | `/profile` | Профіль користувача |
| POST | `/profile/update-nickname` | Зміна нікнейму |
| POST | `/profile/update-password` | Зміна паролю (AJAX) |
| POST | `/profile/update-avatar` | Зміна аватару |
| POST | `/delete-qrs` | Видалення своїх QR (AJAX, JSON) |
| GET | `/get-history-ajax` | Пагінована історія (AJAX, JSON) |
| POST | `/generate` | Генерація QR-коду (AJAX, JSON) |
| GET | `/admin` | Адмін-панель |
| GET | `/admin/get-users-ajax` | Список користувачів (AJAX) |
| GET | `/admin/get-qrs-ajax` | Список QR-кодів (AJAX) |
| POST | `/admin/update-role` | Зміна ролі користувача |
| GET | `/admin/delete-user` | Видалення користувача |
| POST | `/admin/delete-qrs` | Видалення QR (AJAX, JSON) |

---

## 🎨 Система генерації QR-кодів

### Підтримувані типи вмісту

| Тип | Формат вмісту |
|-----|---------------|
| `url` | Пряме URL-посилання |
| `text` | Довільний текст |
| `wifi` | `WIFI:T:{enc};S:{ssid};P:{pass};;` |
| `call` | `tel:{phone}` |
| `vcard` | vCard 3.0 формат |
| `image` | URL до завантаженого зображення (через короткий redirect) |
| `video` | URL до завантаженого відео |
| `pdf` | URL до завантаженого PDF |

### Параметри кастомізації

| Параметр | Варіанти |
|----------|----------|
| Колір точок (`qr_color`) | Будь-який HEX-колір |
| Колір фону (`bg_color`) | Будь-який HEX-колір |
| Форма точок (`qr_style`) | `square`, `rounded`, `circle`, `diamond`, `vertical`, `horizontal` |
| Зовнішні очі (`eye_outer`) | `square`, `rounded`, `circle` |
| Внутрішні очі (`eye_inner`) | `square`, `rounded`, `circle` |
| Розмір (`qr_size`) | 300, 400, 600, 800 px |
| Відступ (`margin`) | 0, 1, 2, 4 модулі |
| Логотип (`qr_logo`) | PNG/JPG/SVG (необов'язково) |

### Процес генерації

1. `QrContentFactory::create()` повертає об'єкт, що реалізує `QrContentInterface`
2. `QrGeneratorService::generate()` через `endroid/qr-code` отримує SVG-матрицю
3. Матриця парситься та перебудовується з урахуванням обраних стилів
4. Зберігаються обидва файли: `.png` (растр) і `.svg` (вектор) у `public/uploads/qr/`
5. У БД фіксується тип, вміст, шлях до файлу, `short_code` та `user_id`

### Конвертація SVG → PNG

- Пріоритет: **Imagick** (висока якість, 300 DPI)
- Резервний варіант: **GD Library** (власна реалізація з підтримкою rect, circle, polygon, image)
- Розмір PNG при експорті: **1000×1000 px**

---

## 🔗 Система коротких посилань та облік сканувань

Кожен згенерований QR-код отримує унікальний `short_code` (8 символів, MD5-похідний). `RedirectController` обробляє перехід: фіксує IP, User-Agent та час у таблиці `scans`, після чого виконує `Location`-redirect на `original_url`.

---

## 📦 Composer-залежності

| Пакет | Версія | Призначення |
|-------|--------|-------------|
| `endroid/qr-code` | v5.x | Генерація базової QR-матриці у форматі SVG |
| `vlucas/phpdotenv` | — | Завантаження змінних середовища з файлу `.env` |

Власний `Autoloader` (PSR-4) зареєстрований через `spl_autoload_register` і завантажує класи з простором імен `App\`.

---

## 🖥️ Вимоги до сервера

| Компонент | Мінімальна версія / вимога |
|-----------|---------------------------|
| PHP | 8.1+ |
| MySQL | 5.7+ / MariaDB 10.3+ |
| GD Library | Обов'язково (`extension=gd`) |
| Imagick | Опційно (покращує якість PNG) |
| Apache / Nginx | Будь-який; mod_rewrite для Apache |
| Composer | 2.x |
| Права на запис | `public/uploads/` |

---

## 🏁 Висновок

GenerQR — повноцінний веб-застосунок, що демонструє практичне застосування чистої MVC-архітектури на PHP без фреймворків. Проєкт охоплює весь цикл розробки: від проєктування бази даних і системи авторизації до низькорівневої векторної графіки та адаптивного UI з темами оформлення.

---
