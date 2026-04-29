<?php

namespace App\Controllers;

use App\Repositories\QrRepository;

class RedirectController {

    public function handle(string $shortCode) {
        $qrRepo = new QrRepository();

        $qr = $qrRepo->getByShortCode($shortCode);

        if ($qr) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $qrRepo->registerScan($qr['id'], $ip, $ua);

            $destination = $qr['original_url'];

            header("Location: " . $destination);
            exit;
        }

        http_response_code(404);
        echo "<h1>404 — Посилання не дійсне</h1>";
        echo "На жаль, цей QR-код не знайдено в нашій системі.";
    }
}