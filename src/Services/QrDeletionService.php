<?php

namespace App\Services;

class QrDeletionService
{
    public function deleteQrFiles(array $qr): void
    {
        if (!empty($qr['media_path'])) {
            $fullPathQr = __DIR__ . '/../../public/' . $qr['media_path'];

            if (file_exists($fullPathQr)) {
                unlink($fullPathQr);
            }
        }

        if (in_array($qr['qr_type'], ['image', 'video'])) {
            $parts = explode('/public/', $qr['original_url']);

            if (isset($parts[1])) {
                $fullPathMedia = __DIR__ . '/../../public/' . $parts[1];

                if (file_exists($fullPathMedia)) {
                    unlink($fullPathMedia);
                }
            }
        }
    }
}