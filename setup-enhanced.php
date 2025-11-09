<?php
/**
 * TinyLink Enhanced - Database Setup
 * Creates database and tables with authentication, analytics, and tier system
 * Run once: http://localhost/TinyLink/setup-enhanced.php
 */

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'tinylink_enhanced';

// Create connection to MySQL server
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$setupMessages = [];
$setupSuccess = false;

try {
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        $setupMessages[] = "✓ Database '$dbname' created or already exists";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select database
    $conn->select_db($dbname);

    // ============ USERS TABLE ============
    $createUsersSql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            username VARCHAR(100) UNIQUE NOT NULL,
            tier ENUM('free', 'pro', 'enterprise') DEFAULT 'free',
            links_limit INT DEFAULT 50,
            links_created INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_tier (tier)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if ($conn->query($createUsersSql) === TRUE) {
        $setupMessages[] = "✓ Table 'users' created successfully";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // ============ TIERS TABLE ============
    $createTiersSql = "
        CREATE TABLE IF NOT EXISTS tiers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            price DECIMAL(10, 2) DEFAULT 0,
            links_limit INT NOT NULL,
            custom_domain BOOLEAN DEFAULT FALSE,
            advanced_analytics BOOLEAN DEFAULT FALSE,
            qr_download BOOLEAN DEFAULT FALSE,
            description TEXT,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if ($conn->query($createTiersSql) === TRUE) {
        $setupMessages[] = "✓ Table 'tiers' created successfully";
    } else {
        throw new Exception("Error creating tiers table: " . $conn->error);
    }

    // Insert default tiers
    $insertTiersSql = "INSERT IGNORE INTO tiers (name, price, links_limit, custom_domain, advanced_analytics, qr_download, description) VALUES
        ('free', 0, 50, FALSE, FALSE, FALSE, 'Basic URL shortener - Perfect for getting started'),
        ('pro', 9.99, 500, TRUE, TRUE, TRUE, 'Advanced features for professionals'),
        ('enterprise', 49.99, 10000, TRUE, TRUE, TRUE, 'Full featured for businesses and teams')";

    if ($conn->query($insertTiersSql) === TRUE) {
        $setupMessages[] = "✓ Default tiers inserted";
    }

    // ============ URLS TABLE (Enhanced) ============
    $createUrlsSql = "
        CREATE TABLE IF NOT EXISTS urls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            long_url LONGTEXT NOT NULL,
            short_code VARCHAR(20) UNIQUE NOT NULL,
            custom_alias VARCHAR(100),
            click_count INT DEFAULT 0,
            qr_code LONGBLOB,
            title VARCHAR(255),
            tags VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_accessed TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_alias (user_id, custom_alias),
            INDEX idx_short_code (short_code),
            INDEX idx_custom_alias (custom_alias),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if ($conn->query($createUrlsSql) === TRUE) {
        $setupMessages[] = "✓ Table 'urls' created successfully";
    } else {
        throw new Exception("Error creating urls table: " . $conn->error);
    }

    // ============ ANALYTICS TABLE ============
    $createAnalyticsSql = "
        CREATE TABLE IF NOT EXISTS analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            url_id INT NOT NULL,
            user_agent VARCHAR(500),
            referrer VARCHAR(500),
            ip_address VARCHAR(45),
            country VARCHAR(100),
            city VARCHAR(100),
            device_type ENUM('mobile', 'tablet', 'desktop', 'other') DEFAULT 'other',
            clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (url_id) REFERENCES urls(id) ON DELETE CASCADE,
            INDEX idx_url_id (url_id),
            INDEX idx_clicked_at (clicked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if ($conn->query($createAnalyticsSql) === TRUE) {
        $setupMessages[] = "✓ Table 'analytics' created successfully";
    } else {
        throw new Exception("Error creating analytics table: " . $conn->error);
    }

    $setupSuccess = true;
    $setupMessages[] = "✓ Database setup completed successfully!";

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
    <title>TinyLink Enhanced - Database Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #2d3748;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            background: <?php echo $setupSuccess ? '#f0fdf4' : '#fef2f2'; ?>;
            border: 2px solid <?php echo $setupSuccess ? '#86efac' : '#fecaca'; ?>;
        }

        .status-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            color: <?php echo $setupSuccess ? '#22863a' : '#991b1b'; ?>;
        }

        .status-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .messages {
            list-style: none;
        }

        .messages li {
            padding: 8px 0;
            color: <?php echo $setupSuccess ? '#165e0f' : '#78350f'; ?>;
            font-size: 0.95rem;
        }

        .messages li:before {
            content: "→ ";
            margin-right: 8px;
            font-weight: bold;
        }

        .success {
            color: #22863a;
        }

        .error {
            color: #d32f2f;
        }

        .features {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }

        .features h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .feature-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            padding: 8px;
            background: #f7fafc;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .feature-item:before {
            content: "✓";
            margin-right: 8px;
            color: #667eea;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .next-steps {
            margin-top: 30px;
            padding: 20px;
            background: #ede9fe;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .next-steps h3 {
            color: #6d28d9;
            margin-bottom: 10px;
        }

        .next-steps ol {
            margin-left: 20px;
            color: #5a3ca7;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .next-steps li {
            margin-bottom: 8px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            transition: background 0.3s;
        }

        .back-link:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 TinyLink Enhanced</h1>
        <p class="subtitle">Database Setup</p>

        <div class="status">
            <div class="status-header">
                <span class="status-icon"><?php echo $setupSuccess ? '✅' : '❌'; ?></span>
                <span><?php echo $setupSuccess ? 'Setup Successful!' : 'Setup Failed'; ?></span>
            </div>
            <ul class="messages">
                <?php foreach ($setupMessages as $message): ?>
                    <li class="<?php echo strpos($message, '✓') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if ($setupSuccess): ?>
            <div class="features">
                <h3>🚀 New Features Created:</h3>
                <div class="feature-list">
                    <div class="feature-item">User Authentication</div>
                    <div class="feature-item">QR Code Generation</div>
                    <div class="feature-item">Click Analytics</div>
                    <div class="feature-item">Tier System</div>
                    <div class="feature-item">Custom Aliases</div>
                    <div class="feature-item">Advanced Tracking</div>
                </div>
            </div>

            <div class="next-steps">
                <h3>📋 Next Steps:</h3>
                <ol>
                    <li>Go to <code>http://localhost/TinyLink</code></li>
                    <li>Create your account</li>
                    <li>Start shortening URLs with QR codes</li>
                    <li>View detailed analytics</li>
                    <li>Upgrade to Pro for custom domains</li>
                </ol>
            </div>

            <a href="http://localhost/TinyLink" class="back-link">Go to TinyLink →</a>
        <?php endif; ?>
    </div>
</body>
</html>
