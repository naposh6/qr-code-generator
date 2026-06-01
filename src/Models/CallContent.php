<?php
namespace App\Models;
use App\Contracts\QrContentInterface;

class CallContent implements QrContentInterface {
    public function __construct(private string $phone) {}
    public function getContent(): string { return "tel:{$this->phone}"; }
    public function getType(): string { return 'call'; }
}