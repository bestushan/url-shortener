<?php
function generateShortCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $shortCode = '';
    for ($i = 0; $i < $length; $i++) {
        $shortCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $shortCode;
}

function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// New function to generate QR code URL
function generateQRCode($url, $size = 150) {
    // Using QR Server API (free, no API key needed)
    $encoded_url = urlencode($url);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded_url}";
}
?>