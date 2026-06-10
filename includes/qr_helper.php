<?php
// includes/qr_helper.php
// Generates a QR code image URL using the free Google Charts-compatible
// QR API (goqr.me) — works without any local library.
// For offline/production use, swap with phpqrcode or endroid/qr-code.

/**
 * Returns an <img> tag with a QR code for the given data.
 */
function qrCodeImg(string $data, int $size = 200): string {
    $encoded = urlencode($data);
    $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}";
    return '<img src="' . htmlspecialchars($url) . '" alt="QR Code" width="' . $size . '" height="' . $size . '">';
}

/**
 * Returns the raw QR image URL (for embedding in PDF / download links).
 */
function qrCodeUrl(string $data, int $size = 200): string {
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
}
