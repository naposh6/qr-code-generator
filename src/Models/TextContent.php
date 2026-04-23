<?php
 namespace App\Models;
 use App\Contracts\QrContentInterface;

 class TextContent implements QrContentInterface {
     private string $text;
     public function __construct(string $text) { $this->text = $text; }
     public function getContent(): string { return $this->text; }
     public function getType(): string { return 'text'; }
 }