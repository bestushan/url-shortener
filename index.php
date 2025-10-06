<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$short_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = trim($_POST['url']);
    $enable_custom = isset($_POST['enable_custom']);
    $custom_code = $enable_custom ? trim($_POST['custom_code']) : '';
    
    if (isValidUrl($original_url)) {
        // Handle custom code
        if ($enable_custom && !empty($custom_code)) {
            if (!isValidCustomCode($custom_code)) {
                $message = "Invalid custom code. Use only letters, numbers, hyphens, underscores (3-20 characters).";
            } elseif (!isCodeAvailable($pdo, $custom_code)) {
                $message = "Custom code '$custom_code' is already taken. Please choose another.";
            } else {
                $short_code = $custom_code;
                $is_custom = true;
            }
        } else {
            // Generate random code
            $is_custom = false;
            do {
                $short_code = generateShortCode();
            } while (!isCodeAvailable($pdo, $short_code));
        }
        
        // Insert into database if no errors
        if (empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO urls (original_url, short_code, is_custom) VALUES (?, ?, ?)");
            $stmt->execute([$original_url, $short_code, $is_custom]);
            
            $short_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/redirect.php?code=" . $short_code;
            $message = "URL shortened successfully!" . ($is_custom ? " (Custom code used)" : "");
        }
    } else {
        $message = "Please enter a valid URL (include http:// or https://)";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="container">
            <div><?php 
    include 'includes/header.php';
    ?></div>
        
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="url-form">
            <input type="url" name="url" placeholder="Enter your long URL (include http://)" required>
            <div class="custom-code-option">
    <label>
        <input type="checkbox" id="enable-custom" name="enable_custom"> 
        Use custom short code (optional)
    </label>
    <div id="custom-code-field" style="display: none; margin-top: 10px;">
        <input type="text" name="custom_code" placeholder="e.g., myshop, sale2024" 
               pattern="[a-zA-Z0-9_-]{3,20}" title="3-20 letters, numbers, hyphens, or underscores">
        <small>Only letters, numbers, hyphens, underscores (3-20 characters)</small>
    </div>
</div>

<script>
document.getElementById('enable-custom').addEventListener('change', function() {
    document.getElementById('custom-code-field').style.display = this.checked ? 'block' : 'none';
});
</script>
            <button type="submit">Shorten URL</button>
        </form>
        
        <?php if ($short_url): ?>
            <div class="result">
                <p>Your shortened URL:</p>
                <div class="short-url">
                    <input type="text" value="<?php echo htmlspecialchars($short_url); ?>" readonly>
                    <button onclick="copyToClipboard()">Copy</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Minimal JavaScript for copy functionality -->
    <script>
    function copyToClipboard() {
        const input = document.querySelector('.short-url input');
        input.select();
        document.execCommand('copy');
        alert('URL copied to clipboard!');
    }
    </script>
</body>
</html>