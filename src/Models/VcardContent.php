<?php
namespace App\Models;
use App\Contracts\QrContentInterface;

class VcardContent implements QrContentInterface {
    public function __construct(private string $name, private string $phone) {}
    public function getContent(): string {
        return "BEGIN:VCARD\nVERSION:3.0\nFN:{$this->name}\nTEL:{$this->phone}\nEND:VCARD";
    }
    public function getType(): string { return 'vcard'; }
}