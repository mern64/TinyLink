<?php
/**
 * TinyLink - Redirect Handler with Click Tracking & Analytics
 * Looks up short code, tracks click, and redirects to original URL
 */

require_once 'config/db.php';

// Get the short code from URL (from URL path parameter)
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/TinyLink/r/';

// Extract short code from URL
if (strpos($request_uri, $base_path) === 0) {
    $short_code = substr($request_uri, strlen($base_path));
    $short_code = trim($short_code, '/');
    $short_code = explode('?', $short_code)[0]; // Remove query string
} else {
    $short_code = isset($_GET['code']) ? trim($_GET['code']) : null;
}

if (!$short_code) {
    // If no code provided, redirect to home
    header('Location: /TinyLink/');
    exit;
}

// Look up the short code in database
$stmt = $conn->prepare("SELECT id, long_url FROM urls WHERE short_code = ? LIMIT 1");
$stmt->bind_param("s", $short_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $url_id = $row['id'];
    $long_url = $row['long_url'];
    
    // Increment click count
    $updateStmt = $conn->prepare("UPDATE urls SET click_count = click_count + 1, last_accessed = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $url_id);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Redirect to original URL
    header('Location: ' . $long_url);
    exit;
} else {
    // Short code not found
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TinyLink - Link Not Found</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .error-container {
                text-align: center;
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            h1 {
                color: #333;
                margin: 0;
            }
            p {
                color: #666;
                margin: 10px 0;
            }
            a {
                color: #667eea;
                text-decoration: none;
                font-weight: 600;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>404 - Link Not Found</h1>
            <p>Sorry, the short link you're looking for doesn't exist.</p>
            <p><a href="index.php">← Back to TinyLink</a></p>
        </div>
    </body>
    </html>
    <?php
}

$stmt->close();
$conn->close();
?>
