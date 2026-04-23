<?php
namespace App\Factories;

use App\Models\UrlContent;
use App\Models\TextContent;
use App\Models\FileContent;
use App\Contracts\QrContentInterface;
use Exception;

class QrContentFactory {
    public static function create(string $type, string $data) : QrContentInterface {
        return match ($type) {
            'url' => new UrlContent($data),
            'text' => new TextContent($data),
            'image', 'video' => new FileContent($data, $type),
            default => throw new Exception("Невідомий тип: {$type}"),
        };
    }
}