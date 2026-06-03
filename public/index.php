<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Database;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\UserController;
use App\Repositories\QrRepository;

Autoloader::register();

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $config = $dotenv->load();
} else {
    $config = [
        'DB_HOST' => 'localhost',
        'DB_NAME' => 'qr_project_db',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'DB_CHARSET' => 'utf8mb4'
    ];
}

try {
    $db = Database::getInstance($config)->getConnection();
} catch (\Exception $e) {
    die("Критична помилка бази даних: " . $e->getMessage());
}

$router = new Router();
$baseDir = $router->getBaseDir();

if (!defined('BASE_DIR')) {
    define('BASE_DIR', $baseDir);
}

if (!defined('APP_URL')) {
    $protocol = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        $protocol = 'https';
    }
    define('APP_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $baseDir);
}

// Головна сторінка
$router->add('/', function($baseDir) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $baseDir . '/login');
        exit;
    }
    $qrRepo = new QrRepository();
    $recentQrs = $qrRepo->getByUserId($_SESSION['user_id'], 5);
    require_once __DIR__ . '/../views/home.php';
});
$router->add('/get-history-ajax', [UserController::class, 'getHistoryAjax']);

// Авторизація
$router->add('/login', function() use ($baseDir) {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . $baseDir . '/');
        exit;
    }
    (new AuthController())->login();
});

$router->add('/register', function() use ($baseDir) {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . $baseDir . '/');
        exit;
    }
    (new AuthController())->register();
});

$router->add('/logout', [AuthController::class, 'logout']);

// Профіль
$router->add('/profile', [UserController::class, 'profile']);
$router->add('/profile/update-password', [UserController::class, 'updatePassword']);
$router->add('/profile/update-nickname', [UserController::class, 'updateNickname']);
$router->add('/profile/update-avatar', [UserController::class, 'updateAvatar']);
$router->add('/delete-qrs', [UserController::class, 'deleteMyQrs']);

// Генерація
$router->add('/generate', function() use ($baseDir) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login');
        exit;
    }
    require_once __DIR__ . '/../views/generate.php';
});

// Адмінка
$router->add('/admin', [AdminController::class, 'dashboard']);
$router->add('/admin/get-users-ajax', [AdminController::class, 'getUsersAjax']);
$router->add('/admin/get-qrs-ajax', [AdminController::class, 'getQrsAjax']);
$router->add('/admin/update-role', [AdminController::class, 'updateRole']);
$router->add('/admin/delete-user', [AdminController::class, 'deleteUser']);
$router->add('/admin/delete-qrs', [AdminController::class, 'deleteQrs']);

$router->run();