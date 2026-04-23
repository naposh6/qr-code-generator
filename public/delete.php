<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Repositories/QrRepository.php';

use App\Repositories\QrRepository;

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $repo = new QrRepository();
        $repo->delete((int)$id);
    } catch (Exception $e) {
        
    }
}

header('Location: /QR-code generator/public/');
exit;