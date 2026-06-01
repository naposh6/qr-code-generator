<?php
namespace App\Factories;

use App\Models\UrlContent;
use App\Models\TextContent;
use App\Models\FileContent;
use App\Models\CallContent;
use App\Models\WifiContent;
use App\Models\VcardContent;
use App\Contracts\QrContentInterface;
use Exception;

class QrContentFactory {
    public static function create(string $type, string $data) : QrContentInterface {
        $payload = json_decode($data, true);
        return match ($type) {
            'url'   => new UrlContent($data),
            'text'  => new TextContent($data),
            'call'  => new CallContent($payload['phone']),
            'wifi'  => new WifiContent($payload['ssid'], $payload['password'], $payload['encryption']),
            'vcard' => new VcardContent($payload['name'], $payload['phone']),
            'image', 'video', 'pdf' => new FileContent($data, $type),
            default => throw new Exception("Невідомий тип: {$type}"),
        };
    }
}