<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Database;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\UserController;

Autoloader::register();

try {
    $db = Database::getInstance()->getConnection();
} catch (\Exception $e) {
    die("Критична помилка бази даних: " . $e->getMessage());
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = urldecode($requestUri);

$scriptName = $_SERVER['SCRIPT_NAME'];
$baseDir = str_replace('/index.php', '', $scriptName);

$path = substr($requestUri, strlen($baseDir));
$path = ($path === '' || $path === '/') ? '/' : $path;

$path = str_replace('/index.php', '', $path);
$path = ($path === '') ? '/' : $path;

$auth = new AuthController();

if ($path === '/login' || $path === '/register') {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . $baseDir . '/');
        exit;
    }

    if ($path === '/login') $auth->login();
    else $auth->register();

 } elseif ($path === '/profile') {
    $userCtrl = new UserController();
    $userCtrl->profile();
} elseif ($path === '/profile/update-password') {
    $userController = new UserController();
    $userController->updatePassword();
 }  elseif ($path === '/admin') {
    $admin = new AdminController();
    $admin->dashboard();
 } elseif ($path === '/admin/get-users-ajax') {
    $admin = new AdminController();
    $admin->getUsersAjax();
 } elseif ($path === '/admin/get-qrs-ajax') {
    $admin = new AdminController();
    $admin->getQrsAjax();
 } elseif ($path === '/admin/update-role') {
    $admin = new AdminController();
    $admin->updateRole();
 } elseif ($path === '/admin/delete-user') {
    $admin = new AdminController();
    $admin->deleteUser();
 }  elseif ($path === '/') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $baseDir . '/login');
        exit;
    }
    require_once __DIR__ . '/../views/home.php';

} elseif ($path === '/logout') {
    $auth->logout();

} elseif ($path === '/generate') {
    if (!isset($_SESSION['user_id'])) { header('Location: login'); exit; }
    require_once __DIR__ . '/../views/generate.php';

} else {
    http_response_code(404);
    echo "<h1>404 - Сторінку не знайдено</h1>";
    echo "<b>DEBUG INFO:</b><br>";
    echo "Повний запит: " . htmlspecialchars($requestUri) . "<br>";
    echo "Базова папка: " . htmlspecialchars($baseDir) . "<br>";
    echo "Очищений шлях: " . htmlspecialchars($path) . "<br>";
    echo '<a href="' . $baseDir . '/">Повернутися на головну</a>';
}