<?php
/**
 * API: Shorten URL
 * Generates unique short code and stores URL mapping in database
 * TinyLink URL Shortener
 */

header('Content-Type: application/json');
require_once '../config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$long_url = isset($data['url']) ? trim($data['url']) : null;

// Validate URL
if (!$long_url) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'URL is required']);
    exit;
}

// Validate URL format
if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
    exit;
}

/**
 * Generate unique short code
 * Uses Base62 encoding (0-9, a-z, A-Z) to create compact codes
 */
function generateUniqueCode($conn) {
    $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $maxAttempts = 100;
    $codeLength = 6;
    
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        // Generate random code
        $short_code = '';
        for ($i = 0; $i < $codeLength; $i++) {
            $short_code .= $charset[rand(0, strlen($charset) - 1)];
        }
        
        // Check if code already exists
        $checkStmt = $conn->prepare("SELECT id FROM urls WHERE short_code = ?");
        $checkStmt->bind_param("s", $short_code);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            // Code is unique
            return $short_code;
        }
        $checkStmt->close();
    }
    
    // Fallback: increase code length if too many collisions
    return generateUniqueCode($conn);
}

try {
    $short_code = generateUniqueCode($conn);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO urls (long_url, short_code, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $long_url, $short_code);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'URL shortened successfully',
            'short_code' => $short_code,
            'short_url' => 'http://localhost/TinyLink/' . $short_code,
            'original_url' => $long_url
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save URL: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>
