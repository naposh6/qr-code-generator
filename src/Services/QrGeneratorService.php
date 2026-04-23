<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

class QrGeneratorService {
    public function generate(QrContentInterface $qrContent): string {

        $qrCode = new QrCode(
            data: $qrContent->getContent(),
            encoding: new Encoding('UTF-8')
        );

        $writer = new PngWriter();

        return $writer->write($qrCode)->getDataUri();
    }
}