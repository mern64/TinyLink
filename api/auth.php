<?php
/**
 * TinyLink - Authentication API
 * Handles user registration, login, and JWT token generation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/db.php';

// JWT Secret key (change this to a secure random string)
define('JWT_SECRET', 'your-super-secret-key-change-this-in-production');

class AuthAPI {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ============ REGISTER ============
    public function register($email, $password, $username) {
        // Validate input
        if (empty($email) || empty($password) || empty($username)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters'];
        }

        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Check if username already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Username already taken'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $stmt = $this->conn->prepare("INSERT INTO users (email, password, username, tier, links_limit) VALUES (?, ?, ?, 'free', 50)");
        $stmt->bind_param("sss", $email, $hashed_password, $username);

        if ($stmt->execute()) {
            $user_id = $this->conn->insert_id;
            return [
                'success' => true,
                'message' => 'Registration successful! Please login.',
                'user_id' => $user_id,
                'email' => $email,
                'username' => $username
            ];
        } else {
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    // ============ LOGIN ============
    public function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        // Get user
        $stmt = $this->conn->prepare("SELECT id, username, password, tier, links_limit, links_created FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        $user = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Generate JWT token
        $token = $this->generateJWT($user['id'], $user['email']);

        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $email,
                'username' => $user['username'],
                'tier' => $user['tier'],
                'links_limit' => $user['links_limit'],
                'links_created' => $user['links_created']
            ]
        ];
    }

    // ============ GENERATE JWT TOKEN ============
    private function generateJWT($user_id, $email) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'user_id' => $user_id,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + (30 * 24 * 60 * 60) // 30 days
        ]));

        $signature = hash_hmac(
            'sha256',
            "$header.$payload",
            JWT_SECRET,
            true
        );
        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    // ============ VERIFY JWT TOKEN ============
    public static function verifyJWT($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        // Verify signature
        $valid_signature = base64_encode(hash_hmac(
            'sha256',
            "$header.$payload",
            JWT_SECRET,
            true
        ));

        if ($signature !== $valid_signature) {
            return null;
        }

        // Decode and verify payload
        $decoded = json_decode(base64_decode($payload), true);

        if (!$decoded || (isset($decoded['exp']) && $decoded['exp'] < time())) {
            return null;
        }

        return $decoded;
    }

    // ============ GET USER FROM TOKEN ============
    public static function getUserFromToken($token) {
        $decoded = self::verifyJWT($token);

        if ($decoded) {
            return [
                'user_id' => $decoded['user_id'],
                'email' => $decoded['email']
            ];
        }

        return null;
    }
}

// ============ HANDLE REQUESTS ============
$auth = new AuthAPI($conn);
$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'register') {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $username = $data['username'] ?? '';
        echo json_encode($auth->register($email, $password, $username));
    } elseif ($action === 'login') {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        echo json_encode($auth->login($email, $password));
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
