<?php

namespace App\Contracts;

interface QrContentInterface {
    public function getContent(): string;
    public function getType(): string;
}