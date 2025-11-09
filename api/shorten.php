<?php
/**
 * TinyLink - URL Shortening API (Enhanced)
 * Generates unique short codes, supports user authentication, and enforces tier limits
 */

header('Content-Type: application/json');
// Allow requests from any origin during development. In production, restrict this to your domain.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
// Allow common headers for authenticated requests
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');

require_once '../config/db.php';
require_once 'auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get authorization token (optional for anonymous users)
$token = '';
$user_id = null;

// Safely get headers - getallheaders might not always be available
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';
} else {
    // Fallback for environments without getallheaders
    $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '';
}

if (!empty($token)) {
    $user_data = AuthAPI::getUserFromToken($token);
    if ($user_data) {
        $user_id = $user_data['user_id'];
    }
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$long_url = isset($data['url']) ? trim($data['url']) : null;
$custom_alias = isset($data['alias']) ? trim($data['alias']) : null;
$title = isset($data['title']) ? trim($data['title']) : null;
$tags = isset($data['tags']) ? trim($data['tags']) : null;

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

// ============ CHECK TIER LIMITS ============
if ($user_id) {
    $stmt = $conn->prepare("
        SELECT u.tier, u.links_limit, u.links_created, t.links_limit as tier_limit
        FROM users u
        JOIN tiers t ON u.tier = t.name
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_info = $stmt->get_result()->fetch_assoc();

    if (!$user_info) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check link limit
    if ($user_info['links_created'] >= $user_info['tier_limit']) {
        http_response_code(402);
        echo json_encode([
            'success' => false,
            'message' => "Link limit reached. You have created {$user_info['links_created']} links.",
            'tier' => $user_info['tier'],
            'links_limit' => $user_info['tier_limit']
        ]);
        exit;
    }
}

/**
 * Generate unique short code
 * Uses Base62 encoding (0-9, a-z, A-Z) to create compact codes
 */
function generateUniqueCode($conn, $user_id = null, $min_length = 6) {
    $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $maxAttempts = 100;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        // Generate random code
        $short_code = '';
        for ($i = 0; $i < $min_length; $i++) {
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

    // Increase length if too many collisions
    return generateUniqueCode($conn, $user_id, $min_length + 1);
}

// ============ VALIDATE CUSTOM ALIAS ============
if ($custom_alias) {
    // Alias validation
    if (strlen($custom_alias) < 3 || strlen($custom_alias) > 50) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alias must be 3-50 characters']);
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $custom_alias)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alias can only contain letters, numbers, dashes, and underscores']);
        exit;
    }

    // Check if alias exists
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id FROM urls WHERE custom_alias = ? AND user_id = ?");
        $stmt->bind_param("si", $custom_alias, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM urls WHERE custom_alias = ?");
        $stmt->bind_param("s", $custom_alias);
    }

    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alias already taken']);
        exit;
    }
}

try {
    $short_code = $custom_alias ?? generateUniqueCode($conn, $user_id);

    // For authenticated users, use user_id; for anonymous, use null
    if ($user_id) {
        $stmt = $conn->prepare("
            INSERT INTO urls (user_id, long_url, short_code, custom_alias, title, tags, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isssss", $user_id, $long_url, $short_code, $custom_alias, $title, $tags);

        // Update user's link count
        $update_stmt = $conn->prepare("UPDATE users SET links_created = links_created + 1 WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
    } else {
        // Anonymous user
        $stmt = $conn->prepare("
            INSERT INTO urls (user_id, long_url, short_code, title, tags, created_at)
            VALUES (NULL, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssss", $long_url, $short_code, $title, $tags);
    }

    if ($stmt->execute()) {
        $url_id = $conn->insert_id;

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'URL shortened successfully',
            'url_id' => $url_id,
            'short_code' => $short_code,
            'short_url' => 'http://localhost/TinyLink/r/' . $short_code,
            'original_url' => $long_url,
            'custom_alias' => $custom_alias
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save URL']);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

$conn->close();
?>
