<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode;

class QrGeneratorService
{
    public function generate(QrContentInterface $qrContent, ?string $savePath = null, array $options = []): array
    {
        $size     = max(100, (int)($options['size']     ?? 400));
        $fgHex    = $options['color']    ?? '#000000';
        $bgHex    = $options['bg_color'] ?? '#ffffff';
        $dotStyle = $options['qr_style'] ?? 'square';
        $eyeOuter = $options['eye_outer'] ?? 'square';
        $eyeInner = $options['eye_inner'] ?? 'square';
        $margin   = max(0, min(10, (int)($options['margin'] ?? 1)));
        $logoPath = $options['logo_path'] ?? null;

        $fgRgb = $this->hexToRgb($fgHex);
        $bgRgb = $this->hexToRgb($bgHex);

        $matrix = $this->getMatrix($qrContent->getContent(), $fgRgb, $bgRgb);
        $n      = count($matrix);

        if ($n < 21) {
            throw new \Exception("Не вдалося побудувати матрицю QR-коду. Перевірте вміст.");
        }

        $svg = $this->buildSvg($matrix, $n, $size, $margin, $fgHex, $bgHex, $dotStyle, $eyeOuter, $eyeInner, $logoPath);

        $pngDataUri = $this->svgToPng($svg, $size, $bgRgb, $fgRgb);

        if ($savePath) {
            $pngBinary = base64_decode(substr($pngDataUri, strlen('data:image/png;base64,')));
            file_put_contents($savePath, $pngBinary);
        }

        return [
            'svg'          => $svg,
            'png_data_uri' => $pngDataUri,
        ];
    }

    private function getMatrix(string $content, array $fgRgb, array $bgRgb): array
    {
        $qrCode = new QrCode(
            data: $content,
            encoding: new Encoding('UTF-8'),
            size: 2100,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $svgWriter = new SvgWriter();
        $svgResult = $svgWriter->write($qrCode);
        $svgString = $svgResult->getString();

        if (!preg_match('/<path\b[^>]*\bd\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $svgString, $pathMatch)) {
            throw new \Exception("Не вдалося знайти path у SVG QR-коду.");
        }
        $d = $pathMatch[1];

        $rects = [];
        $cellSizes = [];

        if (preg_match_all('/M\s*([\d.]+)[,\s]+([\d.]+)\s*h\s*([\d.-]+)\s*v\s*([\d.-]+)/i', $d, $mMatches)) {
            foreach ($mMatches[1] as $i => $x) {
                $h = abs((float)$mMatches[4][$i]);
                $rects[] = [
                    'x' => (float)$x,
                    'y' => (float)$mMatches[2][$i],
                    'w' => abs((float)$mMatches[3][$i]),
                    'h' => $h
                ];
                $cellSizes[] = $h;
            }
        }
        elseif (preg_match_all('/M\s*([\d.]+)[,\s]+([\d.]+)\s*L\s*([\d.]+)[,\s]+([\d.]+)\s*L\s*([\d.]+)[,\s]+([\d.]+)/i', $d, $mMatches)) {
            foreach ($mMatches[1] as $i => $x) {
                $x1 = (float)$x;
                $y1 = (float)$mMatches[2][$i];
                $x2 = (float)$mMatches[3][$i];
                $y3 = (float)$mMatches[6][$i];

                $w = abs($x2 - $x1);
                $h = abs($y3 - $y1);

                $rects[] = ['x' => $x1, 'y' => $y1, 'w' => $w, 'h' => $h];
                $cellSizes[] = $h;
            }
        } else {
            $svgSnippet = substr($svgString, 0, 600);
            $dSnippet   = substr($d, 0, 300);
            throw new \Exception("SVG path не розпізнано. d[0:300]=[$dSnippet] | svg[0:600]=[$svgSnippet]");
        }

        if (empty($rects)) {
            throw new \Exception("Не знайдено модулів у SVG.");
        }

        $roundedHeights = array_map(fn($h) => round($h, 2), $cellSizes);
        $counts = array_count_values(array_map('strval', $roundedHeights));
        arsort($counts);
        $cellSize = (float) array_key_first($counts);
        if ($cellSize < 0.5) $cellSize = 1.0;

        $minX = min(array_column($rects, 'x'));
        $minY = min(array_column($rects, 'y'));

        $maxX = max(array_map(fn($r) => $r['x'] + $r['w'], $rects));
        $n = (int) round(($maxX - $minX) / $cellSize);
        if ($n < 21) $n = 21;

        $matrix = array_fill(0, $n, array_fill(0, $n, false));

        foreach ($rects as $rect) {
            $colStart = (int) round(($rect['x'] - $minX) / $cellSize);
            $rowStart = (int) round(($rect['y'] - $minY) / $cellSize);

            $colCount = (int) round($rect['w'] / $cellSize);
            $rowCount = (int) round($rect['h'] / $cellSize);

            for ($r = 0; $r < $rowCount; $r++) {
                for ($c = 0; $c < $colCount; $c++) {
                    $row = $rowStart + $r;
                    $col = $colStart + $c;
                    if ($row >= 0 && $row < $n && $col >= 0 && $col < $n) {
                        $matrix[$row][$col] = true;
                    }
                }
            }
        }
        return $matrix;
    }

    private function buildSvg(
        array  $matrix,
        int    $n,
        int    $size,
        int    $margin,
        string $fgHex,
        string $bgHex,
        string $dotStyle,
        string $eyeOuter,
        string $eyeInner,
        ?string $logoPath
    ): string {
        $cellSize = $size / ($n + $margin * 2);
        $offsetX  = $margin * $cellSize;
        $offsetY  = $margin * $cellSize;

        $eyePositions = [
            [0,      0     ],
            [0,      $n - 7],
            [$n - 7, 0     ],
        ];

        $shapes = '';

        $drawnEyeOuter = [];
        $drawnEyeInner = [];

        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                if (empty($matrix[$row][$col])) continue;

                $x = $offsetX + $col * $cellSize;
                $y = $offsetY + $row * $cellSize;

                $eyeRole = $this->getEyeRole($row, $col, $eyePositions);

                if ($eyeRole === 'outer') {
                    $key = $this->eyeOriginKey($row, $col, $eyePositions, 0);
                    if ($key !== null && !isset($drawnEyeOuter[$key])) {
                        [$er, $ec] = $eyePositions[$key];
                        $ox = $offsetX + $ec * $cellSize;
                        $oy = $offsetY + $er * $cellSize;
                        $shapes .= $this->drawEyeOuter($ox, $oy, $cellSize, $eyeOuter, $fgHex);
                        $drawnEyeOuter[$key] = true;
                    }
                    continue;
                }

                if ($eyeRole === 'inner') {
                    $key = $this->eyeOriginKey($row, $col, $eyePositions, 2);
                    if ($key !== null && !isset($drawnEyeInner[$key])) {
                        [$er, $ec] = $eyePositions[$key];
                        $ix = $offsetX + ($ec + 2) * $cellSize;
                        $iy = $offsetY + ($er + 2) * $cellSize;
                        $shapes .= $this->drawEyeInner($ix, $iy, $cellSize, $eyeInner, $fgHex);
                        $drawnEyeInner[$key] = true;
                    }
                    continue;
                }

                $shapes .= $this->drawDot($x, $y, $cellSize, $dotStyle, $fgHex);
            }
        }

        $logoSvg = '';
        if ($logoPath && file_exists($logoPath)) {
            $logoSvg = $this->buildLogoSvg($logoPath, $size, $size);
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg"
     width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
  <rect width="{$size}" height="{$size}" fill="{$bgHex}"/>
  {$shapes}
  {$logoSvg}
</svg>
SVG;
    }
    private function getEyeRole(int $row, int $col, array $eps): string
    {
        foreach ($eps as [$er, $ec]) {
            if ($row >= $er && $row < $er + 7 && $col >= $ec && $col < $ec + 7) {
                if ($row >= $er + 2 && $row < $er + 5 && $col >= $ec + 2 && $col < $ec + 5) {
                    return 'inner';
                }
                return 'outer';
            }
        }
        return 'data';
    }

    private function eyeOriginKey(int $row, int $col, array $eps, int $offset): ?int
    {
        foreach ($eps as $i => [$er, $ec]) {
            if ($row >= $er && $row < $er + 7 && $col >= $ec && $col < $ec + 7) {
                return $i;
            }
        }
        return null;
    }

    private function drawDot(float $x, float $y, float $cs, string $style, string $color): string
    {
        $pad = $cs * 0.08;
        $x2  = $x + $pad;
        $y2  = $y + $pad;
        $s   = $cs - $pad * 2;
        $cx  = $x + $cs / 2;
        $cy  = $y + $cs / 2;
        $r   = $s / 2;

        return match ($style) {
            'circle' =>
            "<circle cx='{$cx}' cy='{$cy}' r='{$r}' fill='{$color}'/>",

            'rounded' => (function () use ($x2, $y2, $s, $color) {
                $rr = $s * 0.35;
                return "<rect x='{$x2}' y='{$y2}' width='{$s}' height='{$s}' rx='{$rr}' ry='{$rr}' fill='{$color}'/>";
            })(),

            'diamond' => (function () use ($cx, $cy, $r, $color) {
                $pts = "{$cx}," . ($cy - $r) . " " . ($cx + $r) . ",{$cy} {$cx}," . ($cy + $r) . " " . ($cx - $r) . ",{$cy}";
                return "<polygon points='{$pts}' fill='{$color}'/>";
            })(),

            'star' => (function () use ($cx, $cy, $r, $color) {
                $pts = '';
                for ($i = 0; $i < 8; $i++) {
                    $a  = deg2rad($i * 45 - 22.5);
                    $ri = ($i % 2 === 0) ? $r : $r * 0.5;
                    $pts .= ($cx + $ri * sin($a)) . ',' . ($cy - $ri * cos($a)) . ' ';
                }
                return "<polygon points='{$pts}' fill='{$color}'/>";
            })(),

            'vertical' => (function () use ($x, $y, $cs, $color) {
                $pad = $cs * 0.12;
                $bw  = $cs - $pad * 2;
                return "<rect x='" . ($x + $pad) . "' y='{$y}' width='{$bw}' height='{$cs}' fill='{$color}'/>";
            })(),

            'horizontal' => (function () use ($x, $y, $cs, $color) {
                $pad = $cs * 0.12;
                $bh  = $cs - $pad * 2;
                return "<rect x='{$x}' y='" . ($y + $pad) . "' width='{$cs}' height='{$bh}' fill='{$color}'/>";
            })(),

            default => "<rect x='{$x2}' y='{$y2}' width='{$s}' height='{$s}' fill='{$color}'/>",
        };
    }

    private function drawEyeOuter(float $x, float $y, float $cs, string $style, string $color): string
    {
        $size = $cs * 7;
        $sw   = $cs;

        return match ($style) {
            'circle' => (function () use ($x, $y, $size, $sw, $color) {
                $cx = $x + $size / 2;
                $cy = $y + $size / 2;
                $r  = ($size - $sw) / 2;
                return "<circle cx='{$cx}' cy='{$cy}' r='{$r}' stroke='{$color}' stroke-width='{$sw}' fill='none'/>";
            })(),

            'rounded' => (function () use ($x, $y, $size, $sw, $color) {
                $rr = $size * 0.22;
                $x2 = $x + $sw / 2;
                $y2 = $y + $sw / 2;
                $s2 = $size - $sw;
                return "<rect x='{$x2}' y='{$y2}' width='{$s2}' height='{$s2}' rx='{$rr}' ry='{$rr}' stroke='{$color}' stroke-width='{$sw}' fill='none'/>";
            })(),

            default => (function () use ($x, $y, $size, $sw, $color) {
                $x2 = $x + $sw / 2;
                $y2 = $y + $sw / 2;
                $s2 = $size - $sw;
                return "<rect x='{$x2}' y='{$y2}' width='{$s2}' height='{$s2}' stroke='{$color}' stroke-width='{$sw}' fill='none'/>";
            })(),
        };
    }

    private function drawEyeInner(float $x, float $y, float $cs, string $style, string $color): string
    {
        $size = $cs * 3;

        return match ($style) {
            'circle' => (function () use ($x, $y, $size, $color) {
                $cx = $x + $size / 2;
                $cy = $y + $size / 2;
                $r  = $size / 2;
                return "<circle cx='{$cx}' cy='{$cy}' r='{$r}' fill='{$color}'/>";
            })(),

            'rounded' => (function () use ($x, $y, $size, $color) {
                $rr = $size * 0.28;
                return "<rect x='{$x}' y='{$y}' width='{$size}' height='{$size}' rx='{$rr}' ry='{$rr}' fill='{$color}'/>";
            })(),

            default => "<rect x='{$x}' y='{$y}' width='{$size}' height='{$size}' fill='{$color}'/>",
        };
    }

    private function buildLogoSvg(string $logoPath, int $svgW, int $svgH): string
    {
        $imgData  = file_get_contents($logoPath);
        $b64      = base64_encode($imgData);
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mime     = $finfo->file($logoPath);

        $logoSize = (int)($svgW * 0.22);
        $padding  = (int)($svgW * 0.02);
        $boxSize  = $logoSize + $padding * 2;
        $bx       = ($svgW - $boxSize) / 2;
        $by       = ($svgH - $boxSize) / 2;
        $ix       = ($svgW - $logoSize) / 2;
        $iy       = ($svgH - $logoSize) / 2;
        $rr       = $padding;

        return <<<SVG
  <rect x="{$bx}" y="{$by}" width="{$boxSize}" height="{$boxSize}" rx="{$rr}" ry="{$rr}" fill="white"/>
  <image href="data:{$mime};base64,{$b64}" x="{$ix}" y="{$iy}" width="{$logoSize}" height="{$logoSize}" preserveAspectRatio="xMidYMid meet"/>
SVG;
    }

    private function svgToPng(string $svg, int $size, array $bgRgb, array $fgRgb): string
    {
        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick();
                $im->setBackgroundColor(new \ImagickPixel('transparent'));
                $im->readImageBlob($svg);
                $im->setImageFormat('png32');
                $im->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1);
                $pngData = $im->getImageBlob();
                $im->clear();
                return 'data:image/png;base64,' . base64_encode($pngData);
            } catch (\Exception $e) {

            }
        }

        return $this->fallbackGdPng($svg, $size, $bgRgb, $fgRgb);
    }

    private function fallbackGdPng(string $svg, int $size, array $bgRgb, array $fgRgb): string
    {
        $img = imagecreatetruecolor($size, $size);
        $bg  = imagecolorallocate($img, $bgRgb['r'], $bgRgb['g'], $bgRgb['b']);
        imagefill($img, 0, 0, $bg);

        preg_match_all('/<rect\s([^>]*?)\/>/s', $svg, $rects);
        foreach ($rects[1] as $attrs) {
            $a  = $this->parseAttrs($attrs);
            if (!isset($a['x'], $a['y'], $a['width'], $a['height'])) continue;

            $x  = (int) round((float) $a['x']);
            $y  = (int) round((float) $a['y']);
            $w  = (int) round((float) $a['width']);
            $h  = (int) round((float) $a['height']);

            $stroke = isset($a['stroke']) ? $this->hexToRgb($a['stroke']) : null;
            $sw     = isset($a['stroke-width']) ? max(1, (int) round((float) $a['stroke-width'])) : 0;

            if ($stroke && $sw > 0 && ($a['fill'] ?? '') === 'none') {
                $sc = imagecolorallocate($img, $stroke['r'], $stroke['g'], $stroke['b']);
                for ($t = 0; $t < $sw; $t++) {
                    imagerectangle($img, $x + $t, $y + $t, $x + $w + $t - 1, $y + $h + $t - 1, $sc);
                }
            } else {
                $fillHex = $a['fill'] ?? null;
                $c   = $fillHex && $fillHex !== 'none' ? $this->hexToRgb($fillHex) : $fgRgb;
                $col = imagecolorallocate($img, $c['r'], $c['g'], $c['b']);
                imagefilledrectangle($img, $x, $y, $x + $w - 1, $y + $h - 1, $col);
            }
        }

        preg_match_all('/<circle\s([^>]*?)\/>/s', $svg, $circles);
        foreach ($circles[1] as $attrs) {
            $a  = $this->parseAttrs($attrs);
            $cx = (int) round((float) ($a['cx'] ?? 0));
            $cy = (int) round((float) ($a['cy'] ?? 0));
            $r  = (float) ($a['r'] ?? 0);

            $stroke = isset($a['stroke']) ? $this->hexToRgb($a['stroke']) : null;
            $sw     = isset($a['stroke-width']) ? max(1, (int) round((float) $a['stroke-width'])) : 0;

            if ($stroke && $sw > 0 && ($a['fill'] ?? '') === 'none') {
                $sc = imagecolorallocate($img, $stroke['r'], $stroke['g'], $stroke['b']);
                for ($t = 0; $t < $sw; $t++) {
                    $d = (int) round(($r - $t) * 2);
                    if ($d > 0) imageellipse($img, $cx, $cy, $d, $d, $sc);
                }
            } else {
                $fillHex = $a['fill'] ?? null;
                $c   = $fillHex && $fillHex !== 'none' ? $this->hexToRgb($fillHex) : $fgRgb;
                $col = imagecolorallocate($img, $c['r'], $c['g'], $c['b']);
                $d   = (int) round($r * 2);
                imagefilledellipse($img, $cx, $cy, $d, $d, $col);
            }
        }

        preg_match_all('/<polygon\s([^>]*?)\/>/s', $svg, $polys);
        foreach ($polys[1] as $attrs) {
            $a   = $this->parseAttrs($attrs);
            $pts = trim($a['points'] ?? '');
            if (!$pts) continue;
            $pairs  = preg_split('/[\s,]+/', $pts);
            $coords = [];
            for ($i = 0; $i + 1 < count($pairs); $i += 2) {
                $coords[] = (int) round((float) $pairs[$i]);
                $coords[] = (int) round((float) $pairs[$i + 1]);
            }
            if (count($coords) < 6) continue;
            $fillHex = $a['fill'] ?? null;
            $c   = $fillHex && $fillHex !== 'none' ? $this->hexToRgb($fillHex) : $fgRgb;
            $col = imagecolorallocate($img, $c['r'], $c['g'], $c['b']);
            imagefilledpolygon($img, $coords, (int)(count($coords) / 2), $col);
        }

        ob_start();
        imagepng($img, null, 6);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    private function parseAttrs(string $attrStr): array
    {
        $result = [];
        preg_match_all('/(\w[\w-]*)=[\'"]([^\'"]*)[\'"]/', $attrStr, $m);
        foreach ($m[1] as $i => $k) {
            $result[$k] = $m[2][$i];
        }
        return $result;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        [$r, $g, $b] = sscanf($hex, "%02x%02x%02x");
        return ['r' => $r ?? 0, 'g' => $g ?? 0, 'b' => $b ?? 0];
    }
}