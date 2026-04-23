<?php
namespace App\Models;
use App\Contracts\QrContentInterface;

class FileContent implements QrContentInterface {
    private string $filePath;
    private string $fileType;

    public function __construct(string $filePath, string $fileType) {
        $this->filePath = $filePath;
        $this->fileType = $fileType;
    }

    public function getContent(): string {
        $fileName = basename($this->filePath);
        return "http://localhost/QR-code generator/public/uploads/qr" . $fileName;
    }

    public function getType(): string { return $this->fileType; }
}