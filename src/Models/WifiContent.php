<?php
namespace App\Models;
use App\Contracts\QrContentInterface;

class WifiContent implements QrContentInterface {
    public function __construct(private string $ssid, private string $password, private string $encryption) {}
    public function getContent(): string {
        return "WIFI:T:{$this->encryption};S:{$this->ssid};P:{$this->password};;";
    }
    public function getType(): string { return 'wifi'; }
}