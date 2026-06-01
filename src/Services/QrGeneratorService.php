<?php
namespace App\Services;

use App\Contracts\QrContentInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode;

class QrGeneratorService {

    /**
     * Generate QR code as SVG internally, then:
     *  - save a PNG version to disk (for thumbnails / download)
     *  - return the SVG string so the browser can show crisp vector preview
     *
     * @param QrContentInterface $qrContent
     * @param string|null        $savePath  full path for the PNG file on disk
     * @param array              $options   colour, size, dot style, eye style, logo …
     * @return array  ['png_data_uri' => ..., 'svg' => ...]
     */
    public function generate(QrContentInterface $qrContent, ?string $savePath = null, array $options = []): array
    {
        $size       = (int)($options['size']      ?? 400);
        $fgHex      = $options['color']            ?? '#000000';
        $bgHex      = $options['bg_color']         ?? '#ffffff';
        $dotStyle   = $options['qr_style']         ?? 'square';   // square|circle|rounded|diamond|star
        $eyeOuter   = $options['eye_outer']        ?? 'square';   // square|circle|rounded
        $eyeInner   = $options['eye_inner']        ?? 'square';   // square|circle|rounded
        $logoPath   = $options['logo_path']        ?? null;
        $margin     = (int)($options['margin']     ?? 1);

        $fgRgb = $this->hexToRgb($fgHex);
        $bgRgb = $this->hexToRgb($bgHex);

        // ── 1. Get the raw QR matrix via endroid ──────────────────────────────
        $qrCode = new QrCode(
            data: $qrContent->getContent(),
            encoding: new Encoding('UTF-8'),
            size: $size,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color($fgRgb['r'], $fgRgb['g'], $fgRgb['b']),
            backgroundColor: new Color($bgRgb['r'], $bgRgb['g'], $bgRgb['b'])
        );

        // Use the PNG writer to rasterise for the matrix extraction
        $pngWriter = new PngWriter();
        $pngResult = $pngWriter->write($qrCode);
        $pngData   = $pngResult->getString();

        // Build matrix from PNG pixels
        $srcImg  = imagecreatefromstring($pngData);
        $w       = imagesx($srcImg);
        $h       = imagesy($srcImg);
        $matrix  = $this->extractMatrix($srcImg, $w, $h, $bgRgb);
        $n       = count($matrix);       // number of modules per side
        imagedestroy($srcImg);

        if ($n === 0) {
            throw new \Exception("Не вдалося витягти матрицю QR-коду. Перевірте вміст.");
        }

        // ── 2. Build SVG ──────────────────────────────────────────────────────
        $cellSize  = $size / ($n + $margin * 2);
        $svgWidth  = $size;
        $svgHeight = $size;
        $offsetX   = $margin * $cellSize;
        $offsetY   = $margin * $cellSize;

        $shapes = '';
        $eyePositions = $this->getEyePositions($n);   // top-left, top-right, bottom-left corners

        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                if (!$matrix[$row][$col]) continue;

                $x = $offsetX + $col * $cellSize;
                $y = $offsetY + $row * $cellSize;

                // Is this cell part of an eye?
                $eyeRole = $this->getEyeRole($row, $col, $n, $eyePositions);

                if ($eyeRole === 'outer') {
                    // Outer eye ring — draw once per corner (skip duplicates)
                    if ($this->isEyeCornerOrigin($row, $col, $eyePositions)) {
                        $shapes .= $this->drawEyeOuter($x, $y, $cellSize, $eyeOuter, $fgHex);
                    }
                    continue;
                }

                if ($eyeRole === 'inner') {
                    if ($this->isInnerEyeOrigin($row, $col, $eyePositions)) {
                        $shapes .= $this->drawEyeInner($x, $y, $cellSize, $eyeInner, $fgHex);
                    }
                    continue;
                }

                // Regular data module
                $shapes .= $this->drawDot($x, $y, $cellSize, $dotStyle, $fgHex);
            }
        }

        // Logo overlay
        $logoSvg = '';
        if ($logoPath && file_exists($logoPath)) {
            $logoSvg = $this->buildLogoSvg($logoPath, $svgWidth, $svgHeight);
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
     width="{$svgWidth}" height="{$svgHeight}" viewBox="0 0 {$svgWidth} {$svgHeight}">
  <rect width="{$svgWidth}" height="{$svgHeight}" fill="{$bgHex}"/>
  {$shapes}
  {$logoSvg}
</svg>
SVG;

        // ── 3. Convert SVG → PNG and save ─────────────────────────────────────
        $pngDataUri = $this->svgToPngDataUri($svg, $size, $bgRgb, $fgRgb, $logoPath);

        if ($savePath) {
            // Save PNG to disk (strip data-uri prefix)
            $pngBinary = base64_decode(substr($pngDataUri, strlen('data:image/png;base64,')));
            file_put_contents($savePath, $pngBinary);
        }

        return [
            'png_data_uri' => $pngDataUri,
            'svg'          => $svg,
        ];
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Matrix extraction
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Extract the boolean QR matrix from a rendered PNG image.
     *
     * Key insight: row 0 of every QR code ALWAYS starts with exactly 7 consecutive
     * foreground modules (the top edge of the finder pattern). We use this invariant
     * to reliably measure the cell size regardless of content, version or colours.
     *
     * The old approach sampled the background from pixel (0,0) — wrong when margin=0
     * because that pixel is a dark finder-pattern module. It also scanned the middle
     * row to measure cell size — wrong because consecutive same-value data modules
     * produce runs of 2×, 3×, 7× the true cell size.
     *
     * @param resource $img     GD image resource
     * @param int      $w       Image width in pixels
     * @param int      $h       Image height in pixels
     * @param array    $bgRgb   Known background colour ['r'=>..,'g'=>..,'b'=>..]
     */
    private function extractMatrix($img, int $w, int $h, array $bgRgb): array
    {
        $bgR = $bgRgb['r'];
        $bgG = $bgRgb['g'];
        $bgB = $bgRgb['b'];
        $threshold = 60; // max colour-distance to still be considered "background"

        // Closure: is pixel (x, y) a foreground (non-background) module?
        $isDark = function(int $x, int $y) use ($img, $bgR, $bgG, $bgB, $w, $h, $threshold): bool {
            if ($x < 0 || $x >= $w || $y < 0 || $y >= $h) return false;
            $c = imagecolorat($img, $x, $y);
            $r = ($c >> 16) & 0xFF;
            $g = ($c >>  8) & 0xFF;
            $b =  $c        & 0xFF;
            return (abs($r - $bgR) + abs($g - $bgG) + abs($b - $bgB)) > $threshold;
        };

        // ── Step 1: find the first foreground pixel on row 0 ─────────────────
        // This is the start of the finder pattern (possibly after a tiny margin
        // added by RoundBlockSizeMode::Margin to pad the image to exactly $size px).
        $firstDark = -1;
        for ($x = 0; $x < $w; $x++) {
            if ($isDark($x, 0)) {
                $firstDark = $x;
                break;
            }
        }

        if ($firstDark < 0) {
            return []; // nothing drawn — empty content or wrong colours
        }

        // ── Step 2: measure the first foreground run on row 0 ────────────────
        // The finder pattern top-edge is exactly 7 dark modules in a row, so
        // run_length = 7 × cell_size  →  cell_size = run_length / 7
        $endOfRun = $firstDark;
        for ($x = $firstDark + 1; $x < $w; $x++) {
            if (!$isDark($x, 0)) break;
            $endOfRun = $x;
        }

        $runLength = $endOfRun - $firstDark + 1;   // pixels in 7 modules
        $cellSize  = $runLength / 7.0;
        if ($cellSize < 1.0) $cellSize = 1.0;

        // ── Step 3: compute grid dimensions ──────────────────────────────────
        $marginX = $firstDark;          // left/top margin in pixels (symmetric for QR)
        $marginY = $firstDark;

        // Total modules = (usable width) / cell_size
        $n = (int) round(($w - 2 * $marginX) / $cellSize);
        if ($n < 21) $n = 21; // QR version 1 is the minimum (21×21)

        // ── Step 4: sample each module at its centre ─────────────────────────
        $matrix = [];
        for ($row = 0; $row < $n; $row++) {
            $matrix[$row] = [];
            for ($col = 0; $col < $n; $col++) {
                $px = (int) round($marginX + ($col + 0.5) * $cellSize);
                $py = (int) round($marginY + ($row + 0.5) * $cellSize);
                $matrix[$row][$col] = $isDark($px, $py);
            }
        }

        return $matrix;
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Eye helpers
    // ═════════════════════════════════════════════════════════════════════════

    /** Returns the (row,col) of the top-left cell of each of the 3 finder eyes */
    private function getEyePositions(int $n): array
    {
        return [
            [0, 0],           // top-left
            [0, $n - 7],      // top-right
            [$n - 7, 0],      // bottom-left
        ];
    }

    private function getEyeRole(int $row, int $col, int $n, array $eyePositions): string
    {
        foreach ($eyePositions as [$er, $ec]) {
            // Outer 7×7
            if ($row >= $er && $row < $er + 7 && $col >= $ec && $col < $ec + 7) {
                // Inner 3×3 (centred)
                if ($row >= $er + 2 && $row < $er + 5 && $col >= $ec + 2 && $col < $ec + 5) {
                    return 'inner';
                }
                return 'outer';
            }
        }
        return 'data';
    }

    private function isEyeCornerOrigin(int $row, int $col, array $eyePositions): bool
    {
        foreach ($eyePositions as [$er, $ec]) {
            if ($row === $er && $col === $ec) return true;
        }
        return false;
    }

    private function isInnerEyeOrigin(int $row, int $col, array $eyePositions): bool
    {
        foreach ($eyePositions as [$er, $ec]) {
            if ($row === $er + 2 && $col === $ec + 2) return true;
        }
        return false;
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  SVG shape drawers
    // ═════════════════════════════════════════════════════════════════════════

    private function drawDot(float $x, float $y, float $cs, string $style, string $color): string
    {
        $pad = $cs * 0.08;   // small gap between modules
        $x2  = $x + $pad;
        $y2  = $y + $pad;
        $s   = $cs - $pad * 2;
        $cx  = $x + $cs / 2;
        $cy  = $y + $cs / 2;
        $r   = $s / 2;

        return match ($style) {
            'circle'  => "<circle cx='{$cx}' cy='{$cy}' r='{$r}' fill='{$color}'/>",

            'rounded' => (function() use ($x2, $y2, $s, $color) {
                $rr = $s * 0.35;
                return "<rect x='{$x2}' y='{$y2}' width='{$s}' height='{$s}' rx='{$rr}' ry='{$rr}' fill='{$color}'/>";
            })(),

            'diamond' => (function() use ($cx, $cy, $r, $color) {
                $pts = "{$cx}," . ($cy - $r) . " " . ($cx + $r) . ",{$cy} {$cx}," . ($cy + $r) . " " . ($cx - $r) . ",{$cy}";
                return "<polygon points='{$pts}' fill='{$color}'/>";
            })(),

            'star' => (function() use ($cx, $cy, $r, $color) {
                $pts = '';
                for ($i = 0; $i < 8; $i++) {
                    $a = deg2rad($i * 45 - 22.5);
                    $ri = ($i % 2 === 0) ? $r : $r * 0.5;
                    $pts .= ($cx + $ri * sin($a)) . ',' . ($cy - $ri * cos($a)) . ' ';
                }
                return "<polygon points='{$pts}' fill='{$color}'/>";
            })(),

            'vertical' => (function() use ($x, $y, $cs, $color) {
                // Vertical bar (like bars in a barcode)
                $pad = $cs * 0.12;
                $bw  = $cs - $pad * 2;
                $bh  = $cs;
                return "<rect x='" . ($x + $pad) . "' y='{$y}' width='{$bw}' height='{$bh}' fill='{$color}'/>";
            })(),

            'horizontal' => (function() use ($x, $y, $cs, $color) {
                $pad = $cs * 0.12;
                $bh  = $cs - $pad * 2;
                return "<rect x='{$x}' y='" . ($y + $pad) . "' width='{$cs}' height='{$bh}' fill='{$color}'/>";
            })(),

            default => "<rect x='{$x2}' y='{$y2}' width='{$s}' height='{$s}' fill='{$color}'/>",  // square
        };
    }

    private function drawEyeOuter(float $x, float $y, float $cs, string $style, string $color): string
    {
        $size = $cs * 7;
        $sw   = $cs;          // stroke / border width

        return match ($style) {
            'circle' => (function() use ($x, $y, $size, $sw, $color) {
                $cx = $x + $size / 2;
                $cy = $y + $size / 2;
                $r  = ($size - $sw) / 2;
                return "<circle cx='{$cx}' cy='{$cy}' r='{$r}' stroke='{$color}' stroke-width='{$sw}' fill='none'/>";
            })(),

            'rounded' => (function() use ($x, $y, $size, $sw, $color) {
                $rr  = $size * 0.22;
                $x2  = $x + $sw / 2;
                $y2  = $y + $sw / 2;
                $s2  = $size - $sw;
                return "<rect x='{$x2}' y='{$y2}' width='{$s2}' height='{$s2}' rx='{$rr}' ry='{$rr}' stroke='{$color}' stroke-width='{$sw}' fill='none'/>";
            })(),

            default => (function() use ($x, $y, $size, $sw, $color) {
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
            'circle' => (function() use ($x, $y, $size, $color) {
                $cx = $x + $size / 2;
                $cy = $y + $size / 2;
                $r  = $size / 2;
                return "<circle cx='{$cx}' cy='{$cy}' r='{$r}' fill='{$color}'/>";
            })(),

            'rounded' => (function() use ($x, $y, $size, $color) {
                $rr = $size * 0.28;
                return "<rect x='{$x}' y='{$y}' width='{$size}' height='{$size}' rx='{$rr}' ry='{$rr}' fill='{$color}'/>";
            })(),

            default => "<rect x='{$x}' y='{$y}' width='{$size}' height='{$size}' fill='{$color}'/>",
        };
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Logo
    // ═════════════════════════════════════════════════════════════════════════

    private function buildLogoSvg(string $logoPath, int $svgW, int $svgH): string
    {
        $imgData   = file_get_contents($logoPath);
        $b64       = base64_encode($imgData);
        $finfo     = new \finfo(FILEINFO_MIME_TYPE);
        $mime      = $finfo->file($logoPath);

        $logoSize  = (int)($svgW * 0.22);
        $padding   = (int)($svgW * 0.02);
        $boxSize   = $logoSize + $padding * 2;
        $bx        = ($svgW - $boxSize) / 2;
        $by        = ($svgH - $boxSize) / 2;
        $ix        = ($svgW - $logoSize) / 2;
        $iy        = ($svgH - $logoSize) / 2;
        $rr        = $padding;

        return <<<SVG
  <rect x="{$bx}" y="{$by}" width="{$boxSize}" height="{$boxSize}" rx="{$rr}" ry="{$rr}" fill="white"/>
  <image href="data:{$mime};base64,{$b64}" x="{$ix}" y="{$iy}" width="{$logoSize}" height="{$logoSize}" preserveAspectRatio="xMidYMid meet"/>
SVG;
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  SVG → PNG conversion (GD-based)
    // ═════════════════════════════════════════════════════════════════════════

    private function svgToPngDataUri(string $svg, int $size, array $bgRgb, array $fgRgb, ?string $logoPath): string
    {
        // Strategy: use Inkscape/rsvg if available, else fall back to re-rendering with GD
        // We try imagick first (usually available in WAMP/LAMP setups)
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
                // fall through to GD
            }
        }

        // GD fallback: re-render the QR using endroid PNG writer (original rasteriser)
        // but apply the same colour settings. The SVG shapes are cosmetic on the web;
        // the PNG stored on disk uses the endroid PNG (good enough for scanning).
        // We use the same PNG we already generated from the QrCode object.
        // Since we don't have it here, we reuse what was passed as $pngData above.
        // Actually we do have it via the matrix — so regenerate simply:
        return $this->fallbackPng($svg, $size, $bgRgb, $fgRgb);
    }

    /**
     * GD-based fallback: parse the SVG shapes and draw them on a GD canvas.
     * Handles rect, circle, polygon (simplified — covers all our shapes).
     */
    private function fallbackPng(string $svg, int $size, array $bgRgb, array $fgRgb): string
    {
        $img = imagecreatetruecolor($size, $size);
        $bg  = imagecolorallocate($img, $bgRgb['r'], $bgRgb['g'], $bgRgb['b']);
        $fg  = imagecolorallocate($img, $fgRgb['r'], $fgRgb['g'], $fgRgb['b']);
        imagefill($img, 0, 0, $bg);

        // Parse <rect> elements
        preg_match_all('/<rect\s([^\/]*?)\/>/s', $svg, $rects);
        foreach ($rects[1] as $attrs) {
            $a = $this->parseAttrs($attrs);
            if (!isset($a['x'])) continue;
            $x  = (int)round((float)$a['x']);
            $y  = (int)round((float)$a['y']);
            $w  = (int)round((float)($a['width']  ?? 0));
            $h  = (int)round((float)($a['height'] ?? 0));
            $rx = (int)round((float)($a['rx']     ?? 0));
            $c  = isset($a['fill']) ? $this->hexToRgb($a['fill']) : $fgRgb;
            $col = imagecolorallocate($img, $c['r'], $c['g'], $c['b']);

            $stroke = isset($a['stroke']) ? $this->hexToRgb($a['stroke']) : null;
            $sw     = isset($a['stroke-width']) ? (int)round((float)$a['stroke-width']) : 0;

            if ($stroke && $sw > 0) {
                // Hollow stroked rect (eye outer)
                $sc = imagecolorallocate($img, $stroke['r'], $stroke['g'], $stroke['b']);
                for ($t = 0; $t < $sw; $t++) {
                    imagerectangle($img, $x + $t, $y + $t, $x + $w - $t, $y + $h - $t, $sc);
                }
            } else {
                imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $col);
            }
        }

        // Parse <circle> elements
        preg_match_all('/<circle\s([^\/]*?)\/>/s', $svg, $circles);
        foreach ($circles[1] as $attrs) {
            $a  = $this->parseAttrs($attrs);
            $cx = (int)round((float)($a['cx'] ?? 0));
            $cy = (int)round((float)($a['cy'] ?? 0));
            $r  = (int)round((float)($a['r']  ?? 0));

            $stroke = isset($a['stroke']) ? $this->hexToRgb($a['stroke']) : null;
            $sw     = isset($a['stroke-width']) ? (int)round((float)$a['stroke-width']) : 0;

            if ($stroke && $sw > 0) {
                // Draw ring
                $sc = imagecolorallocate($img, $stroke['r'], $stroke['g'], $stroke['b']);
                for ($t = 0; $t < $sw; $t++) {
                    imageellipse($img, $cx, $cy, ($r - $t) * 2, ($r - $t) * 2, $sc);
                }
            } else {
                $c   = isset($a['fill']) ? $this->hexToRgb($a['fill']) : $fgRgb;
                $col = imagecolorallocate($img, $c['r'], $c['g'], $c['b']);
                imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $col);
            }
        }

        // Parse <polygon> (diamond / star)
        preg_match_all('/<polygon\s([^\/]*?)\/>/s', $svg, $polys);
        foreach ($polys[1] as $attrs) {
            $a    = $this->parseAttrs($attrs);
            $pts  = trim($a['points'] ?? '');
            if (!$pts) continue;
            $pairs = preg_split('/[\s,]+/', $pts);
            $coords = [];
            for ($i = 0; $i < count($pairs) - 1; $i += 2) {
                $coords[] = (int)round((float)$pairs[$i]);
                $coords[] = (int)round((float)$pairs[$i + 1]);
            }
            $c   = isset($a['fill']) ? $this->hexToRgb($a['fill']) : $fgRgb;
            $col = imagecolorallocate($img, $c['r'], $c['g'], $c['b']);
            imagefilledpolygon($img, $coords, count($coords) / 2, $col);
        }

        ob_start();
        imagepng($img, null, 6);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Utilities
    // ═════════════════════════════════════════════════════════════════════════

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
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        [$r, $g, $b] = sscanf($hex, "%02x%02x%02x");
        return ['r' => $r ?? 0, 'g' => $g ?? 0, 'b' => $b ?? 0];
    }
}