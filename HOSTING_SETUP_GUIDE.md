# 🚀 TinyLink - Hosting & Deployment Guide

This guide walks you through deploying TinyLink to your subdomain. Follow these steps to get your URL shortener live!

---

## 📋 Pre-Deployment Checklist

Before you start, ensure you have:

- ✅ A subdomain purchased and pointing to your hosting server (DNS configured)
- ✅ Access to your hosting control panel (cPanel, Plesk, etc.)
- ✅ SSH/SFTP access to your server
- ✅ PHP 7.0+ support
- ✅ MySQL 5.7+ database access
- ✅ Apache with `mod_rewrite` enabled

---

## 🔧 Files to Modify for Hosting

The following files contain hardcoded local paths and need to be updated for your subdomain:

### **CRITICAL FILES TO EDIT:**

1. **`config/db.php`** - Database configuration
2. **`api/shorten.php`** - URL generation with base path
3. **`api/qrcode.php`** - QR code generation with base path
4. **`assets/js/app.js`** - API endpoints and navigation paths
5. **`.htaccess`** - URL rewriting rules
6. **`redirect.php`** - Redirect base path
7. **`index-enhanced.html`** - Navigation links
8. **`login.html`** - API endpoints and redirects
9. **`pricing.html`** - Navigation links

---

## 📝 Step-by-Step Configuration


1. **`config/db.php`** - Database configuration
2. **`api/shorten.php`** - URL generation with base path
3. **`api/qrcode.php`** - QR code generation with base path
4. **`assets/js/app.js`** - API endpoints and navigation paths
5. **`.htaccess`** - URL rewriting rules
6. **`redirect.php`** - Redirect base path
7. **`index.html`** - Navigation links
8. **`login.html`** - API endpoints and redirects
9. **`pricing.html`** - Navigation links

---

## 📝 Step-by-Step Configuration

### **Step 1: Update Database Configuration**
```

**File:** `config/db.php`

Change your database connection to use your hosting provider's database details:

```php
<?php
// Database credentials - UPDATE THESE FOR PRODUCTION
$host = 'your-hosting-db-server';      // Usually: localhost, 127.0.0.1, or provided by host
$username = 'your_db_username';         // Database username
$password = 'your_db_password';         // Database password (keep secure!)
$dbname = 'your_database_name';         // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
?>
```

**Where to find these details:**
- Check your hosting provider's control panel (cPanel, Plesk, etc.)
- Usually listed under: Database Management, MySQL, phpMyAdmin
- Database host is often `localhost` on shared hosting

---

### **Step 2: Update .htaccess for Your Domain Path**

**File:** `.htaccess`

The `.htaccess` file controls URL rewriting. Update the `RewriteBase` according to your setup:

#### **Option A: Site at Domain Root** (`yoursubdomain.com/`)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Prevent direct access to hidden files
    RewriteRule "^\." - [F]

    # Route /r/* requests to redirect.php
    RewriteCond %{REQUEST_URI} ^/r/(.+)$
    RewriteRule ^r/(.+)$ redirect.php?code=$1 [QSA,L]

    # Route short codes to redirect.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !^/(api|assets|config|index|login|dashboard|pricing|setup|redirect|r)
    RewriteRule ^([a-zA-Z0-9_-]+)$ redirect.php?code=$1 [QSA,L]
</IfModule>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

#### **Option B: Site in Subdirectory** (`yourdomain.com/tinylink/`)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /tinylink/

    # Prevent direct access to hidden files
    RewriteRule "^\." - [F]

    # Route /r/* requests to redirect.php
    RewriteCond %{REQUEST_URI} ^/tinylink/r/(.+)$
    RewriteRule ^r/(.+)$ redirect.php?code=$1 [QSA,L]

    # Route short codes to redirect.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !^/tinylink/(api|assets|config|index|login|dashboard|pricing|setup|redirect|r)
    RewriteRule ^([a-zA-Z0-9_-]+)$ redirect.php?code=$1 [QSA,L]
</IfModule>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

---

### **Step 3: Update Base Paths in PHP Files**

#### **`redirect.php`**

Update the `$base_path` variable:

```php
// For domain root
$base_path = '/r/';

// OR for subdirectory
$base_path = '/tinylink/r/';
```

---

### **Step 4: Update API Endpoints**

#### **`api/shorten.php` (Line ~198)**

Find and update the `short_url` generation:

```php
// Change from:
'short_url' => 'http://localhost/TinyLink/r/' . $short_code,

// To:
'short_url' => 'https://yoursubdomain.com/r/' . $short_code,

// Or for subdirectory:
'short_url' => 'https://yourdomain.com/tinylink/r/' . $short_code,
```

#### **`api/qrcode.php` (Lines ~111 and ~149)**

Update both occurrences:

```php
// Change from:
$short_url = "http://localhost/TinyLink/r/" . $url['short_code'];

// To:
$short_url = "https://yoursubdomain.com/r/" . $url['short_code'];

// Or for subdirectory:
$short_url = "https://yourdomain.com/tinylink/r/" . $url['short_code'];
```

---

### **Step 5: Update Frontend Navigation Paths**

**Files:** `index.html`, `login.html`, `pricing.html`

Replace all instances of `/TinyLink/` with your new base path:

#### **For domain root**, change:
```html
<!-- From -->
<a href="/TinyLink/pricing.html">
const API_BASE = '/TinyLink/api';

<!-- To -->
<a href="/pricing.html">
const API_BASE = '/api';
```

#### **For subdirectory**, change:
```html
<!-- From -->
<a href="/TinyLink/pricing.html">
const API_BASE = '/TinyLink/api';

<!-- To -->
<a href="/tinylink/pricing.html">
const API_BASE = '/tinylink/api';
```

---

### **Step 6: Update JavaScript API Base**

#### **`assets/js/app.js`** (Line ~370)

Change the API base URL. Find this line:

```javascript
// Change from:
const API_BASE = '/TinyLink/api';

// To (domain root):
const API_BASE = '/api';

// Or to (subdirectory):
const API_BASE = '/tinylink/api';
```

Also update the file:// protocol warning (line ~68):

```javascript
// Update both mentions of localhost to your domain
'Please open this page at: https://yoursubdomain.com/index.html\n\n' +
```

---

### **Step 7: Update Error Messages & Documentation Files**

#### **`setup.php`** (Lines ~5, ~9, ~371, ~379)

Update references to localhost URLs to point to your new domain:

```php
// From:
* Run once: http://localhost/TinyLink/setup.php
$host = 'localhost';
<li>Go to <code>http://localhost/TinyLink</code></li>

// To:
* Run once: https://yoursubdomain.com/setup.php
$host = 'your-hosting-db-server';  // Database host
<li>Go to <code>https://yoursubdomain.com</code></li>
```

---

## 🚀 Deployment Steps

### **1. Create Database on Hosting**

- Log into your hosting control panel (cPanel, Plesk, etc.)
- Go to **MySQL / Databases** section
- Create a new database (e.g., `tinylink_prod`)
- Create a database user with a strong password
- Grant all privileges to the user on the database

### **2. Upload Files via SFTP/SSH**

```bash
# Connect to your server
sftp your-user@your-hosting-server

# Navigate to public_html (or web root)
cd public_html

# Upload all TinyLink files (or use your subdirectory)
put -r /path/to/TinyLink .
```

### **3. Initialize Database**

- Update `config/db.php` with your hosting database credentials
- Update `setup.php` database details
- Visit `https://yoursubdomain.com/setup.php` in your browser
- Follow the setup wizard to initialize the database

### **4. Verify Installation**

- Check `https://yoursubdomain.com` loads correctly
- Try creating a shortened link
- Test login functionality
- Verify redirects work (`https://yoursubdomain.com/r/SHORT_CODE`)

---

## 🔒 Security Considerations

### **1. Protect Sensitive Files**

Add this to `.htaccess` to prevent direct access to config files:

```apache
# Deny access to config files
<FilesMatch "^config">
    Order allow,deny
    Deny from all
</FilesMatch>

# Deny access to setup files after initial setup
<FilesMatch "^setup">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### **2. Use HTTPS**

- Ensure your SSL certificate is properly installed (usually free with cPanel/Plesk)
- Update all hardcoded URLs to use `https://` instead of `http://`
- Force HTTPS in `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### **3. Protect Database Credentials**

- **NEVER** hardcode production credentials in public files
- Use environment variables (if your host supports it)
- Change database password from default
- Use strong, unique passwords

### **4. Update Database Host**

On most shared hosting, the database host is `localhost`, but verify with your hosting provider:

```php
// Most common for shared hosting:
$host = 'localhost';

// Sometimes:
$host = '127.0.0.1';

// Or your host might provide a specific server address
$host = 'db.example.com';
```

---

## 📝 Configuration Template

Create a **config file template** for easy deployment across environments:

**`config/config.example.php`** (commit this to git, but NOT `config/db.php`):

```php
<?php
// RENAME THIS FILE TO config.php IN PRODUCTION
// NEVER commit real credentials to version control

// ===== LOCAL DEVELOPMENT =====
// $database = [
//     'host' => 'localhost',
//     'user' => 'root',
//     'pass' => '',
//     'name' => 'tinylink_enhanced',
//     'protocol' => 'http://localhost/TinyLink',
// ];

// ===== PRODUCTION HOSTING =====
$database = [
    'host' => 'your-hosting-db-server',
    'user' => 'your_db_user',
    'pass' => 'your_db_password',
    'name' => 'your_database_name',
    'protocol' => 'https://yoursubdomain.com',
];
?>
```

---

## 🧪 Testing Checklist

After deployment, verify all functionality:

- ✅ Homepage loads (`https://yoursubdomain.com`)
- ✅ Can create shortened links anonymously
- ✅ Can create account and login
- ✅ Can create shortened links when authenticated
- ✅ Custom aliases work
- ✅ QR codes generate correctly
- ✅ Shortened links redirect properly (`/r/CODE`)
- ✅ Click analytics increment
- ✅ Dashboard displays correctly
- ✅ HTTPS works (no mixed content warnings)
- ✅ All navigation links work
- ✅ Pricing page loads correctly

---

## 🐛 Troubleshooting

### **404 Errors on Shortened Links**

- Check `.htaccess` is uploaded and `RewriteBase` is correct
- Verify `mod_rewrite` is enabled on your hosting
- Check file permissions (644 for files, 755 for directories)

### **Database Connection Failed**

- Verify database credentials in `config/db.php`
- Check database host (usually `localhost` for shared hosting)
- Ensure database user has all required privileges
- Run setup script again: `https://yoursubdomain.com/setup.php`

### **API Requests Failing**

- Check `API_BASE` constant in `assets/js/app.js`
- Verify all API URLs in HTML files point to correct path
- Check CORS headers in API files if accessed from different domain

### **Links Generated with Wrong Domain**

- Check `short_url` generation in `api/shorten.php`
- Check QR code URL generation in `api/qrcode.php`
- Both should use correct domain/subdomain

---

## 📞 Need Help?

If you encounter issues during deployment, check:

1. Hosting provider's documentation
2. Error logs (usually in `error_log` file in public_html)
3. Database connection details
4. File permissions (644 files, 755 directories)
5. Apache module configuration

---

**Next Steps:** Once you provide your subdomain, I can generate exact configuration files tailored to your setup! 🎯

