<?php
namespace App\Models;
use App\Contracts\QrContentInterface;

class FileContent implements QrContentInterface {
    private string $fullUrl;
    private string $fileType;

    public function __construct(string $fullUrl, string $fileType) {
        $this->fullUrl = $fullUrl;
        $this->fileType = $fileType;
    }

    public function getContent(): string {
        return $this->fullUrl;
    }

    public function getType(): string { return $this->fileType; }
}