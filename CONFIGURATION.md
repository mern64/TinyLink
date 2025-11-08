# TinyLink Configuration Examples

This file contains useful configuration examples and snippets for customizing TinyLink.

## 1. Custom Short Code Length

### Problem
You want short codes to be longer or shorter than the default 6 characters.

### Solution
Edit: `/api/shorten.php` around line 50

```php
// Current (6 characters)
$codeLength = 6;

// For shorter codes (5 characters - more chance of collision)
$codeLength = 5;

// For longer codes (8 characters - less chance of collision)
$codeLength = 8;

// For maximum compatibility (3 characters - simple codes)
$codeLength = 3;
```

**Recommendation**: 6 characters provides a good balance
- Can create ~56 billion unique codes
- Collision probability is extremely low
- URLs remain short and shareable

---

## 2. Database Credentials

### Problem
You need to connect to a different MySQL user or server.

### Solution
Edit: `/config/db.php`

```php
// Default configuration (local XAMPP)
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'tinylink';

// Example: Remote MySQL server
$host = '192.168.1.100';
$username = 'urlshortener';
$password = 'secure_password_here';
$dbname = 'tinylink_production';

// Example: Different port
$host = 'localhost:3307';  // Custom MySQL port
$username = 'root';
$password = '';
$dbname = 'tinylink';
```

---

## 3. Custom Domain Base URL

### Problem
You want to use a custom domain for short links instead of `localhost`.

### Solution
Edit: `/api/shorten.php` around line 85

```php
// Current (localhost)
'short_url' => 'http://localhost/TinyLink/' . $short_code

// Example: Production domain
'short_url' => 'https://shortlink.com/' . $short_code

// Example: Subdomain
'short_url' => 'https://link.example.com/' . $short_code

// Example: Short domain
'short_url' => 'https://sln.co/' . $short_code
```

**Important**: Also update the `.htaccess` file if using a different path structure.

---

## 4. Character Set for Short Codes

### Problem
You want to use a different character set (e.g., no lowercase, no numbers).

### Solution
Edit: `/api/shorten.php` around line 35

```php
// Current (Base62 - most compact)
$charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Numbers and uppercase only
$charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Uppercase letters only (easier to type)
$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Hex characters only (0-9, A-F)
$charset = '0123456789ABCDEF';

// Avoid confusing characters (no 0/O, no 1/I/l)
$charset = '23456789ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
```

**Recommendation**: Use Base62 (current setting) for maximum compactness.

---

## 5. Error Handling

### Problem
You want to log errors to a file instead of showing them.

### Solution
Edit: `/api/shorten.php` - Wrap try-catch block

```php
try {
    // ... existing code ...
} catch (Exception $e) {
    // Log error to file
    $error_log = fopen('../logs/errors.log', 'a');
    fwrite($error_log, date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n");
    fclose($error_log);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
```

**Create logs folder first**:
```bash
mkdir /Applications/XAMPP/xamppfiles/htdocs/TinyLink/logs
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/TinyLink/logs
```

---

## 6. Rate Limiting

### Problem
You want to limit URL shortening requests per IP address.

### Solution
Add to `/api/shorten.php` at the beginning:

```php
// Rate limiting
function checkRateLimit($conn, $ip_address) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM urls WHERE created_by_ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Limit to 100 shortened URLs per hour per IP
    if ($result['count'] >= 100) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many requests. Try again later.']);
        exit;
    }
    $stmt->close();
}

// Use it
$ip_address = $_SERVER['REMOTE_ADDR'];
checkRateLimit($conn, $ip_address);

// Also add column to database:
// ALTER TABLE urls ADD COLUMN created_by_ip VARCHAR(45);
```

---

## 7. HTTPS Enforcement

### Problem
You want to enforce HTTPS for all requests.

### Solution
Edit: `/.htaccess`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Rest of the rules...
    RewriteBase /TinyLink/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^([a-zA-Z0-9]+)$ redirect.php?code=$1 [QSA,L]
</IfModule>
```

---

## 8. Custom Headers

### Problem
You want to add custom security headers.

### Solution
Edit: `/.htaccess`

```apache
<IfModule mod_headers.c>
    # Prevent MIME type sniffing
    Header set X-Content-Type-Options "nosniff"
    
    # Prevent clickjacking
    Header set X-Frame-Options "SAMEORIGIN"
    
    # Enable XSS protection
    Header set X-XSS-Protection "1; mode=block"
    
    # CORS headers (if needed)
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "POST, OPTIONS"
    
    # Cache control
    Header set Cache-Control "public, max-age=3600"
</IfModule>
```

---

## 9. Database Query Optimization

### Problem
Your database is getting slow with many URLs.

### Solution
Add these indexes to `/setup.php`:

```sql
-- Speed up lookups
CREATE INDEX idx_short_code ON urls(short_code);
CREATE INDEX idx_created_at ON urls(created_at);

-- For analytics queries
CREATE INDEX idx_click_count ON urls(click_count DESC);

-- For time-range queries
CREATE INDEX idx_date_range ON urls(created_at, last_accessed);

-- For cleanup (if you add expiration)
CREATE INDEX idx_expiration ON urls(expiration_date);
```

---

## 10. Adding Link Expiration

### Problem
You want links to expire after a certain time.

### Solution
1. Add to database:
```sql
ALTER TABLE urls ADD COLUMN expiration_date TIMESTAMP NULL DEFAULT NULL;
```

2. Update `/redirect.php`:
```php
// After fetching the URL, check expiration
if ($row['expiration_date'] !== null && time() > strtotime($row['expiration_date'])) {
    http_response_code(410);  // 410 Gone
    echo "This link has expired";
    exit;
}
```

3. Update `/api/shorten.php`:
```php
// Allow optional expiration parameter
$expiration = isset($data['expiration_days']) ? 
    date('Y-m-d H:i:s', strtotime('+' . $data['expiration_days'] . ' days')) : 
    null;

$stmt = $conn->prepare("INSERT INTO urls (long_url, short_code, expiration_date, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $long_url, $short_code, $expiration);
```

---

## 11. Adding URL Category/Tags

### Problem
You want to organize URLs by categories.

### Solution
1. Add to database:
```sql
ALTER TABLE urls ADD COLUMN category VARCHAR(50) DEFAULT 'general';
```

2. Update `/api/shorten.php`:
```php
$category = isset($data['category']) ? trim($data['category']) : 'general';
$stmt = $conn->prepare("INSERT INTO urls (long_url, short_code, category, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $long_url, $short_code, $category);
```

---

## 12. Analytics Query Examples

Use phpMyAdmin or MySQL client to run these queries:

```sql
-- Top 10 most clicked links
SELECT short_code, long_url, click_count, created_at 
FROM urls 
ORDER BY click_count DESC 
LIMIT 10;

-- Recent links
SELECT short_code, long_url, click_count, created_at 
FROM urls 
ORDER BY created_at DESC 
LIMIT 20;

-- Links created today
SELECT short_code, long_url, click_count 
FROM urls 
WHERE DATE(created_at) = CURDATE();

-- Total statistics
SELECT 
    COUNT(*) as total_links,
    SUM(click_count) as total_clicks,
    AVG(click_count) as avg_clicks,
    MAX(click_count) as max_clicks
FROM urls;

-- Active links (clicked in last 7 days)
SELECT short_code, long_url, click_count, last_accessed 
FROM urls 
WHERE last_accessed > DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY last_accessed DESC;
```

---

## 13. Backup Database

### Problem
You want to backup your database.

### Solution
```bash
# Backup database
mysqldump -u root -p tinylink > tinylink_backup.sql

# Restore database
mysql -u root -p tinylink < tinylink_backup.sql

# Export to CSV (for analysis)
SELECT short_code, long_url, click_count, created_at 
INTO OUTFILE '/tmp/tinylink_export.csv'
FIELDS TERMINATED BY ','
FROM urls;
```

---

## 14. Clean Old Links

### Problem
You want to remove expired or old links.

### Solution
```sql
-- Delete links older than 1 year with less than 10 clicks
DELETE FROM urls 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR) 
AND click_count < 10;

-- Delete never-accessed links older than 30 days
DELETE FROM urls 
WHERE last_accessed IS NULL 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 15. Environment-Based Configuration

### Problem
You have different settings for dev, staging, and production.

### Solution
Create `/config/environment.php`:

```php
<?php
// Define environment
define('ENVIRONMENT', getenv('APP_ENV') ?? 'development');

// Load environment-specific config
if (ENVIRONMENT === 'production') {
    $host = 'prod-db.example.com';
    $username = 'prod_user';
    $password = 'secure_password';
    $dbname = 'tinylink_prod';
    $debug = false;
} elseif (ENVIRONMENT === 'staging') {
    $host = 'staging-db.example.com';
    $username = 'staging_user';
    $password = 'staging_password';
    $dbname = 'tinylink_staging';
    $debug = false;
} else {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'tinylink';
    $debug = true;
}
?>
```

Then use in `config/db.php`:
```php
require_once 'environment.php';
// Use $host, $username, $password, $dbname
```

---

## 🎯 Quick Reference

| Configuration | File | Line | Default |
|---|---|---|---|
| Short Code Length | `api/shorten.php` | ~50 | 6 |
| Character Set | `api/shorten.php` | ~35 | Base62 |
| Database Name | `config/db.php` | 6 | tinylink |
| DB Host | `config/db.php` | 3 | localhost |
| DB User | `config/db.php` | 4 | root |
| DB Password | `config/db.php` | 5 | (empty) |
| Base URL | `api/shorten.php` | 85 | localhost/TinyLink |

---

Happy configuring! 🚀
