<?php

require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Database;

Autoloader::register();

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database: Successful <br>";
} catch (\Exception $e) {
    die("Connected to database failed: " . $e->getMessage());
}

echo "Welcome to GenerQR!";