<?php
/**
 * TinyLink - Contact Form Handler
 * Handles contact form submissions and stores in database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/db.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Handle GET request (for testing)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'getMessages') {
        // Get all contact messages (for admin)
        $stmt = $conn->prepare("
            SELECT id, name, email, subject, message, created_at, status
            FROM contact_messages
            ORDER BY created_at DESC
            LIMIT 100
        ");
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            echo json_encode(['success' => true, 'messages' => $messages]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Query failed']);
        }
    }
    exit;
}

// Handle POST request (submit contact form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $subject = trim($data['subject'] ?? '');
    $message = trim($data['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = 'Name must be between 2 and 100 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    } elseif (strlen($subject) < 5 || strlen($subject) > 200) {
        $errors[] = 'Subject must be between 5 and 200 characters';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) < 10 || strlen($message) > 5000) {
        $errors[] = 'Message must be between 10 and 5000 characters';
    }
    
    // Check for spam (simple rate limiting)
    $ip = $_SERVER['REMOTE_ADDR'];
    $spam_check = $conn->prepare("
        SELECT COUNT(*) as count FROM contact_messages 
        WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $spam_check->bind_param("s", $ip);
    $spam_check->execute();
    $spam_result = $spam_check->get_result()->fetch_assoc();
    
    if ($spam_result['count'] >= 5) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many messages from this IP. Please try again later.']);
        exit;
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Sanitize inputs
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
    // Insert into database
    try {
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, subject, message, ip_address, status)
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        
        $stmt->bind_param("sssss", $name, $email, $subject, $message, $ip);
        
        if ($stmt->execute()) {
            $message_id = $stmt->insert_id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Thank you! Your message has been received. We will get back to you soon.',
                'message_id' => $message_id
            ]);
            
            // Optional: Send email notification
            // sendEmailNotification($name, $email, $subject, $message);
            
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    
    exit;
}

// Invalid request
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
