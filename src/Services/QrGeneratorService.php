<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrGeneratorService {
    public function generate(QrContentInterface $qrContent): string {
        $qrCode = QrCode::create($qrContent->getContent())
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter();
        return $writer->write($qrCode)->getDataUri();
    }
}