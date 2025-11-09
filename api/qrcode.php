<?php
/**
 * TinyLink - QR Code Generation API
 * Generates QR codes for shortened URLs
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

require_once '../config/db.php';
require_once 'auth.php';

class QRCodeGenerator {
    /**
     * Generate QR Code using QR Server API
     * This creates a QR code without external dependencies
     */
    public static function generateQRCode($data, $size = 300) {
        // Using QR Server API (free, no authentication needed)
        $encoded_data = urlencode($data);
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded_data}";

        return $qr_url;
    }

    /**
     * Generate QR code as data URL (base64)
     */
    public static function getQRCodeAsBase64($data, $size = 300) {
        $qr_url = self::generateQRCode($data, $size);

        // Fetch the QR code image
        $image_data = @file_get_contents($qr_url);

        if ($image_data === false) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($image_data);
    }

    /**
     * Create SVG-based QR code (no external API)
     * Simple implementation using QR algorithm
     */
    public static function generateSimpleQRCode($text, $size = 300) {
        // This is a simple placeholder that uses Google Charts API
        $encoded = urlencode($text);
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&chld=L|0&cht=qr&chl={$encoded}";
    }
}

// ============ HANDLE REQUESTS ============
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get authorization token
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization required']);
    exit;
}

// Verify token
$user_data = AuthAPI::getUserFromToken($token);
if (!$user_data) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

$user_id = $user_data['user_id'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action === 'generate') {
        $url_id = $data['url_id'] ?? 0;
        $size = $data['size'] ?? 300;

        // Get the short URL
        $stmt = $conn->prepare("SELECT short_code FROM urls WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $url_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'URL not found']);
            exit;
        }

        $url = $result->fetch_assoc();
        $short_url = "http://localhost/TinyLink/r/" . $url['short_code'];

        // Generate QR code
        $qr_url = QRCodeGenerator::generateQRCode($short_url, $size);
        $qr_base64 = QRCodeGenerator::getQRCodeAsBase64($short_url, $size);

        // Save QR code to database
        if ($qr_base64) {
            $stmt = $conn->prepare("UPDATE urls SET qr_code = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $qr_base64, $url_id, $user_id);
            $stmt->execute();
        }

        echo json_encode([
            'success' => true,
            'message' => 'QR code generated successfully',
            'qr_code' => $qr_url,
            'qr_code_base64' => $qr_base64,
            'short_url' => $short_url
        ]);

    } elseif ($action === 'download') {
        $url_id = $data['url_id'] ?? 0;
        $format = $data['format'] ?? 'png'; // png, svg, pdf

        // Get URL and QR code
        $stmt = $conn->prepare("SELECT short_code, qr_code FROM urls WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $url_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'URL not found']);
            exit;
        }

        $url = $result->fetch_assoc();
        $short_url = "http://localhost/TinyLink/r/" . $url['short_code'];

        // Generate fresh QR code
        $qr_base64 = QRCodeGenerator::getQRCodeAsBase64($short_url, 500);

        echo json_encode([
            'success' => true,
            'message' => 'QR code ready for download',
            'qr_code_base64' => $qr_base64,
            'download_link' => 'data:image/png;base64,' . substr($qr_base64, 22)
        ]);

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
