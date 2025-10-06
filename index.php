<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$short_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = trim($_POST['url']);
    
    if (isValidUrl($original_url)) {
        // Check if URL already exists
        $stmt = $pdo->prepare("SELECT short_code FROM urls WHERE original_url = ?");
        $stmt->execute([$original_url]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $short_code = $existing['short_code'];
        } else {
            // Generate unique short code
            do {
                $short_code = generateShortCode();
                $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
                $stmt->execute([$short_code]);
            } while ($stmt->fetch());
            
            // Insert new URL
            $stmt = $pdo->prepare("INSERT INTO urls (original_url, short_code) VALUES (?, ?)");
            $stmt->execute([$original_url, $short_code]);
        }
        
        $short_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/redirect.php?code=" . $short_code;
        $message = "URL shortened successfully!";
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
            <button type="submit">Shorten URL</button>
        </form>
        
        <?php if ($short_url): ?>
    <div class="result">
        <p>Your shortened URL:</p>
        <div class="short-url">
            <input type="text" value="<?php echo htmlspecialchars($short_url); ?>" readonly>
            <button onclick="copyToClipboard()">Copy</button>
        </div>
        
        <!-- ADD QR CODE SECTION HERE -->
        <div class="qr-section">
            <h3>📱 QR Code</h3>
            <div class="qr-container">
                <img src="<?php echo generateQRCode($short_url); ?>" 
                     alt="QR Code for <?php echo htmlspecialchars($short_url); ?>"
                     class="qr-code">
            </div>
            <div class="qr-actions">
                <button onclick="downloadQRCode('<?php echo generateQRCode($short_url, 300); ?>', '<?php echo $short_code; ?>')">
                    Download QR Code
                </button>
            </div>
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

    function downloadQRCode(qrUrl, filename) {
    // Create a temporary link to download the QR code
    const link = document.createElement('a');
    link.href = qrUrl;
    link.download = `qrcode-${filename}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
function downloadQRCode(qrUrl, filename) {
    fetch(qrUrl)
        .then(response => response.blob())
        .then(blob => {
            // Create a temporary link
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `qrcode-${filename}.png`;
            
            // Append to body, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up the URL object
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Download failed:', error);
            alert('Download failed. Please try again.');
        });
}
// Preview larger QR code on hover
document.addEventListener('DOMContentLoaded', function() {
    const qrCode = document.querySelector('.qr-code');
    if (qrCode) {
        qrCode.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        qrCode.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    }
});
    </script>
</body>
</html>