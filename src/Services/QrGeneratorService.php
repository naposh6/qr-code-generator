<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode;

class QrGeneratorService {

    public function generate(QrContentInterface $qrContent, string $savePath = null, array $options = []): string {
        $size = (int)($options['size'] ?? 400);
        $rgb = $this->hexToRgb($options['color'] ?? '#000000');
        $bgRgb = $this->hexToRgb($options['bg_color'] ?? '#ffffff');

        $qrCode = new QrCode(
            data: $qrContent->getContent(),
            encoding: new Encoding('UTF-8'),
            size: $size,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color($rgb['r'], $rgb['g'], $rgb['b']),
            backgroundColor: new Color($bgRgb['r'], $bgRgb['g'], $bgRgb['b'])
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $imageData = $result->getString();

        if (($options['qr_style'] ?? 'square') === 'circle') {
            $src = imagecreatefromstring($imageData);
            $w = imagesx($src);
            $h = imagesy($src);

            $img = imagecreatetruecolor($w, $h);
            imagealphablending($img, true);
            imagesavealpha($img, true);

            $dotColor = imagecolorallocate($img, $rgb['r'], $rgb['g'], $rgb['b']);
            $bgColor = imagecolorallocate($img, $bgRgb['r'], $bgRgb['g'], $bgRgb['b']);
            imagefill($img, 0, 0, $bgColor);

            $blockSize = 1;
            for ($x = 0; $x < $w; $x++) {
                if (imagecolorat($src, $x, (int)($h / 2)) !== imagecolorat($src, 0, 0)) {
                    $start = $x;
                    while ($x < $w && imagecolorat($src, $x, (int)($h / 2)) !== imagecolorat($src, 0, 0)) $x++;
                    $blockSize = $x - $start;
                    break;
                }
            }

            for ($y = 0; $y < $h; $y += $blockSize) {
                for ($x = 0; $x < $w; $x += $blockSize) {
                    $centerX = (int)($x + ($blockSize / 2));
                    $centerY = (int)($y + ($blockSize / 2));
                    if ($centerX >= $w || $centerY >= $h) continue;

                    if (imagecolorat($src, $centerX, $centerY) !== imagecolorat($src, 0, 0)) {
                        imagefilledellipse($img, $centerX, $centerY, (int)$blockSize, (int)$blockSize, $dotColor);
                    }
                }
            }

            if (!empty($options['logo_path']) && file_exists($options['logo_path'])) {
                $logoSrc = imagecreatefromstring(file_get_contents($options['logo_path']));
                if ($logoSrc) {
                    $logoW = imagesx($logoSrc);
                    $logoH = imagesy($logoSrc);

                    $destW = (int)($w * 0.22);
                    $destH = (int)($logoH * ($destW / $logoW));

                    $destX = (int)(($w - $destW) / 2);
                    $destY = (int)(($h - $destH) / 2);

                    $white = imagecolorallocate($img, 255, 255, 255);
                    imagefilledrectangle($img, $destX - 2, $destY - 2, $destX + $destW + 2, $destY + $destH + 2, $white);

                    imagecopyresampled($img, $logoSrc, $destX, $destY, 0, 0, $destW, $destH, $logoW, $logoH);
                    imagedestroy($logoSrc);
                }
            }

            ob_start();
            imagepng($img);
            $imageData = ob_get_clean();
            imagedestroy($src);
            imagedestroy($img);
        }

        if ($savePath) {
            file_put_contents($savePath, $imageData);
        }

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    private function hexToRgb($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
        return ['r' => $r ?? 0, 'g' => $g ?? 0, 'b' => $b ?? 0];
    }
}