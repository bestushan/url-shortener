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

// NEW: Custom code validation
function isValidCustomCode($code) {
    return preg_match('/^[a-zA-Z0-9_-]+$/', $code) && strlen($code) >= 3 && strlen($code) <= 20;
}

// NEW: Check if code is available
function isCodeAvailable($pdo, $code) {
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
    $stmt->execute([$code]);
    return !$stmt->fetch();
}
?>