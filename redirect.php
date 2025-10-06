<?php
require_once 'config/database.php';

if (isset($_GET['code'])) {
    $short_code = $_GET['code'];
    
    // Find the original URL
    $stmt = $pdo->prepare("SELECT original_url FROM urls WHERE short_code = ?");
    $stmt->execute([$short_code]);
    $url = $stmt->fetch();
    
    if ($url) {
        // Update click count
        $stmt = $pdo->prepare("UPDATE urls SET click_count = click_count + 1 WHERE short_code = ?");
        $stmt->execute([$short_code]);
        
        // Redirect to original URL
        header("Location: " . $url['original_url']);
        exit;
    }
}

// If code not found or invalid
header("Location: index.php");
exit;
?>