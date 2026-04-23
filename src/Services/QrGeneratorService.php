<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

class QrGeneratorService {
    public function generate(QrContentInterface $qrContent, ?string $savePath = null): string {

        $qrCode = new QrCode(
            data: $qrContent->getContent(),
            encoding: new Encoding('UTF-8')
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        if ($savePath) {
            $result->saveToFile($savePath);
        }

        return $result->getDataUri();
    }
}