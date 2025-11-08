<?php
/**
 * Database Configuration
 * TinyLink URL Shortener
 */

// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'tinylink';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

?>
