<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Database;

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

if ($path === '/') {
    require_once __DIR__ . '/../views/home.php';
} elseif ($path === '/generate') {
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