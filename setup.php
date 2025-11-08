<?php
/**
 * Database Setup Script
 * Creates the necessary database and table for TinyLink
 * Run this script once from your browser: http://localhost/TinyLink/setup.php
 */

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'tinylink';

// Connect to MySQL server (without specific database)
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$setupMessages = [];

try {
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        $setupMessages[] = "✓ Database '$dbname' created or already exists";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($dbname);

    // Create urls table with all necessary fields
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS urls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            long_url LONGTEXT NOT NULL,
            short_code VARCHAR(10) NOT NULL UNIQUE,
            click_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_accessed TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_short_code (short_code),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if ($conn->query($createTableSql) === TRUE) {
        $setupMessages[] = "✓ Table 'urls' created successfully";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }

    $setupSuccess = true;

} catch (Exception $e) {
    $setupSuccess = false;
    $setupMessages[] = "✗ Error: " . $e->getMessage();
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TinyLink - Database Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        
        h1 {
            color: #2d3748;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        
        .status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .status.success {
            background: #f0fff4;
            border: 2px solid #48bb78;
        }
        
        .status.error {
            background: #fff5f5;
            border: 2px solid #f56565;
        }
        
        .status h2 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }
        
        .status.success h2 {
            color: #22543d;
        }
        
        .status.error h2 {
            color: #742a2a;
        }
        
        .messages {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .messages li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 1rem;
        }
        
        .messages li:last-child {
            border-bottom: none;
        }
        
        .status.success .messages li {
            color: #22543d;
        }
        
        .status.error .messages li {
            color: #742a2a;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
            border: 2px solid #cbd5e0;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .info-box {
            background: #edf2f7;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 0.95rem;
            color: #2d3748;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 TinyLink Setup</h1>
        
        <div class="status <?php echo $setupSuccess ? 'success' : 'error'; ?>">
            <h2><?php echo $setupSuccess ? '✓ Setup Complete!' : '✗ Setup Failed'; ?></h2>
            <ul class="messages">
                <?php foreach ($setupMessages as $message): ?>
                    <li><?php echo htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <?php if ($setupSuccess): ?>
            <div class="info-box">
                <strong>📝 Database Details:</strong><br>
                • Database: <code>tinylink</code><br>
                • Table: <code>urls</code><br>
                • Fields: id, long_url, short_code, click_count, created_at, last_accessed<br>
                • Indexes: short_code (for fast lookups), created_at (for sorting)
            </div>
            
            <div class="button-group">
                <a href="index.php" class="btn btn-primary">Go to TinyLink →</a>
            </div>
        <?php else: ?>
            <div class="info-box">
                <strong>⚠️ Troubleshooting:</strong><br>
                1. Make sure XAMPP MySQL is running<br>
                2. Verify database credentials in setup.php<br>
                3. Check that the 'root' user can access MySQL without a password<br>
                4. Refresh this page and try again
            </div>
            
            <div class="button-group">
                <button class="btn btn-secondary" onclick="location.reload()">Try Again</button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
