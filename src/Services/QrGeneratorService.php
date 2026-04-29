<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;

class QrGeneratorService {

    public function generate(QrContentInterface $qrContent, string $savePath = null, array $options = []): string {

        $size = isset($options['size']) ? (int)$options['size'] : 400;
        $hexColor = isset($options['color']) ? $options['color'] : '#000000';
        $rgb = $this->hexToRgb($hexColor);

        $foregroundColor = new Color($rgb['r'], $rgb['g'], $rgb['b']);
        $backgroundColor = new Color(255, 255, 255);

        $qrCode = new QrCode(
            data: $qrContent->getContent(),
            encoding: new Encoding('UTF-8'),
            size: $size,
            margin: 10,
            foregroundColor: $foregroundColor,
            backgroundColor: $backgroundColor
        );

        $writer = new PngWriter();

        $result = $writer->write($qrCode);

        if ($savePath) {
            $result->saveToFile($savePath);
        }

        return $result->getDataUri();
    }

    private function hexToRgb($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
        return [
            'r' => $r ?? 0,
            'g' => $g ?? 0,
            'b' => $b ?? 0
        ];
    }
}