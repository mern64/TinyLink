<?php
/**
 * TinyLink - Analytics API
 * Provides detailed analytics for shortened URLs
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

require_once '../config/db.php';
require_once 'auth.php';

class Analytics {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ============ TRACK CLICK ============
    public function trackClick($url_id, $user_agent = '', $referrer = '', $ip = '') {
        // Detect device type
        $device_type = $this->detectDeviceType($user_agent);

        // Get geolocation data (optional - from IP)
        $geo_data = $this->getGeodata($ip);

        $stmt = $this->conn->prepare("
            INSERT INTO analytics (url_id, user_agent, referrer, ip_address, country, city, device_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $country = $geo_data['country'] ?? 'Unknown';
        $city = $geo_data['city'] ?? 'Unknown';

        $stmt->bind_param(
            "issssss",
            $url_id,
            $user_agent,
            $referrer,
            $ip,
            $country,
            $city,
            $device_type
        );

        if ($stmt->execute()) {
            // Update click count
            $stmt = $this->conn->prepare("UPDATE urls SET click_count = click_count + 1, last_accessed = NOW() WHERE id = ?");
            $stmt->bind_param("i", $url_id);
            $stmt->execute();

            return ['success' => true];
        }

        return ['success' => false];
    }

    // ============ GET URL ANALYTICS ============
    public function getURLAnalytics($url_id, $user_id) {
        // Verify user owns this URL
        $stmt = $this->conn->prepare("SELECT id FROM urls WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $url_id, $user_id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'URL not found'];
        }

        // Get total clicks
        $stmt = $this->conn->prepare("SELECT click_count, created_at, last_accessed FROM urls WHERE id = ?");
        $stmt->bind_param("i", $url_id);
        $stmt->execute();
        $url_data = $stmt->get_result()->fetch_assoc();

        // Get clicks by device type
        $stmt = $this->conn->prepare("
            SELECT device_type, COUNT(*) as count
            FROM analytics
            WHERE url_id = ?
            GROUP BY device_type
        ");
        $stmt->bind_param("i", $url_id);
        $stmt->execute();
        $device_results = $stmt->get_result();
        $device_data = [];
        while ($row = $device_results->fetch_assoc()) {
            $device_data[$row['device_type']] = $row['count'];
        }

        // Get clicks by country
        $stmt = $this->conn->prepare("
            SELECT country, COUNT(*) as count
            FROM analytics
            WHERE url_id = ? AND country != 'Unknown'
            GROUP BY country
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $url_id);
        $stmt->execute();
        $country_results = $stmt->get_result();
        $country_data = [];
        while ($row = $country_results->fetch_assoc()) {
            $country_data[] = $row;
        }

        // Get clicks by referrer
        $stmt = $this->conn->prepare("
            SELECT referrer, COUNT(*) as count
            FROM analytics
            WHERE url_id = ? AND referrer != '' AND referrer IS NOT NULL
            GROUP BY referrer
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $url_id);
        $stmt->execute();
        $referrer_results = $stmt->get_result();
        $referrer_data = [];
        while ($row = $referrer_results->fetch_assoc()) {
            $referrer_data[] = $row;
        }

        // Get daily stats (last 30 days)
        $stmt = $this->conn->prepare("
            SELECT DATE(clicked_at) as date, COUNT(*) as count
            FROM analytics
            WHERE url_id = ? AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(clicked_at)
            ORDER BY date
        ");
        $stmt->bind_param("i", $url_id);
        $stmt->execute();
        $daily_results = $stmt->get_result();
        $daily_data = [];
        while ($row = $daily_results->fetch_assoc()) {
            $daily_data[] = $row;
        }

        return [
            'success' => true,
            'total_clicks' => $url_data['click_count'],
            'created_at' => $url_data['created_at'],
            'last_accessed' => $url_data['last_accessed'],
            'device_distribution' => $device_data,
            'top_countries' => $country_data,
            'top_referrers' => $referrer_data,
            'daily_stats' => $daily_data
        ];
    }

    // ============ GET ALL URLS ANALYTICS (DASHBOARD) ============
    public function getAllURLsAnalytics($user_id) {
        // Get all URLs for user
        $stmt = $this->conn->prepare("
            SELECT id, short_code, long_url, click_count, created_at, title
            FROM urls
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();

        $urls = [];
        $total_clicks = 0;
        $total_urls = 0;

        while ($row = $results->fetch_assoc()) {
            $urls[] = $row;
            $total_clicks += $row['click_count'];
            $total_urls++;
        }

        // Get top 5 URLs
        $stmt = $this->conn->prepare("
            SELECT id, short_code, long_url, click_count, title
            FROM urls
            WHERE user_id = ?
            ORDER BY click_count DESC
            LIMIT 5
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $top_urls = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'success' => true,
            'total_urls' => $total_urls,
            'total_clicks' => $total_clicks,
            'avg_clicks_per_url' => $total_urls > 0 ? round($total_clicks / $total_urls, 2) : 0,
            'all_urls' => $urls,
            'top_urls' => $top_urls
        ];
    }

    // ============ DETECT DEVICE TYPE ============
    private function detectDeviceType($user_agent) {
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', strtolower($user_agent))) {
            return 'tablet';
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|ios)/i', strtolower($user_agent))) {
            return 'mobile';
        }
        if (preg_match('/(windows|mac|linux)/i', strtolower($user_agent))) {
            return 'desktop';
        }
        return 'other';
    }

    // ============ GET GEOLOCATION DATA ============
    private function getGeodata($ip) {
        // Using free geolocation API
        // In production, use a proper GeoIP database
        if ($ip === '127.0.0.1' || $ip === 'localhost') {
            return ['country' => 'Local', 'city' => 'Localhost'];
        }

        // Uncomment for real geolocation (requires API call)
        // $geo_data = @json_decode(@file_get_contents("http://ip-api.com/json/$ip"), true);
        // return [
        //     'country' => $geo_data['country'] ?? 'Unknown',
        //     'city' => $geo_data['city'] ?? 'Unknown'
        // ];

        return ['country' => 'Unknown', 'city' => 'Unknown'];
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

// For tracking clicks, we don't need auth
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'track' && $method === 'POST') {
    // Tracking endpoint (no auth required)
    $data = json_decode(file_get_contents('php://input'), true);
    $url_id = $data['url_id'] ?? 0;

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $analytics = new Analytics($conn);
    echo json_encode($analytics->trackClick($url_id, $user_agent, $referrer, $ip));
    exit;
}

// For other actions, require authentication
if (empty($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization required']);
    exit;
}

$user_data = AuthAPI::getUserFromToken($token);
if (!$user_data) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

$user_id = $user_data['user_id'];

if ($method === 'GET') {
    $analytics = new Analytics($conn);

    if ($action === 'url') {
        $url_id = isset($_GET['url_id']) ? (int)$_GET['url_id'] : 0;
        echo json_encode($analytics->getURLAnalytics($url_id, $user_id));
    } elseif ($action === 'dashboard') {
        echo json_encode($analytics->getAllURLsAnalytics($user_id));
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
