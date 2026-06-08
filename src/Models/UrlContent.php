<?php
 namespace App\Models;

 use App\Contracts\QrContentInterface;

 class UrlContent implements QrContentInterface {
     private string $url;
     public function __construct(string $url) { $this->url = $url; }
     public function getContent(): string { return $this->url; }
     public function getType(): string { return 'url'; }
 }