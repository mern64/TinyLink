# 🎯 Quick Hosting Checklist - Before You Deploy

## ⚡ 5-Minute Summary

Before uploading to your subdomain, you need to update **9 critical files** to replace:
- `localhost` with your domain
- `/TinyLink/` with your base path
- Database credentials

---

## 📋 Files to Edit (Quick Reference)

| # | File | What to Change | Find | Replace With |
|---|------|----------------|------|--------------|
| 1 | `config/db.php` | Database host & credentials | `$host = 'localhost'` | Your hosting DB details |
| 2 | `.htaccess` | URL rewriting base path | `RewriteBase /TinyLink/` | Your path (e.g., `/`) |
| 3 | `api/shorten.php` | Short URL generation | `http://localhost/TinyLink/r/` | `https://yourdomain.com/r/` |
| 4 | `api/qrcode.php` | QR code URL (2 places) | `http://localhost/TinyLink/r/` | `https://yourdomain.com/r/` |
| 5 | `assets/js/app.js` | API endpoints | `/TinyLink/api` | `/api` or `/tinylink/api` |
| 6 | `redirect.php` | Redirect base path | `'/TinyLink/r/'` | `'/r/'` or `'/tinylink/r/'` |
| 7 | `index.html` | Navigation links | `/TinyLink/` | `/` or `/tinylink/` |
| 8 | `login.html` | Navigation links | `/TinyLink/` | `/` or `/tinylink/` |
| 9 | `pricing.html` | Navigation links | `/TinyLink/` | `/` or `/tinylink/` |

---

## 🌐 Two Common Setups

### **Setup A: Site at Domain Root** (Recommended)
```
yoursubdomain.com/  ← Files uploaded here
yoursubdomain.com/index.html
yoursubdomain.com/r/ABC123  ← Redirects here
```

**Use these replacements:**
- `RewriteBase /` (in .htaccess)
- `const API_BASE = '/api'` (in app.js)
- `'short_url' => 'https://yoursubdomain.com/r/'` (in APIs)

---

### **Setup B: Site in Subdirectory**
```
yourdomain.com/tinylink/  ← Files uploaded here
yourdomain.com/tinylink/index.html
yourdomain.com/tinylink/r/ABC123  ← Redirects here
```

**Use these replacements:**
- `RewriteBase /tinylink/` (in .htaccess)
- `const API_BASE = '/tinylink/api'` (in app.js)
- `'short_url' => 'https://yourdomain.com/tinylink/r/'` (in APIs)

---

## 🔐 Database Setup

1. **Create database** in your hosting control panel (cPanel, etc.)
2. **Update `config/db.php`:**
   ```php
   $host = 'localhost';  // or your hosting provider's DB server
   $username = 'your_db_user';
   $password = 'your_db_password';
   $dbname = 'your_database_name';
   ```
3. **Run setup script:** Visit `https://yoursubdomain.com/setup.php`

---

## 🚀 Deployment Checklist

- [ ] Choose Setup A or B (domain root vs subdirectory)
- [ ] Note your exact domain/subdomain
- [ ] Create database on hosting
- [ ] Update `config/db.php` with DB credentials
- [ ] Update `.htaccess` RewriteBase
- [ ] Update API base URLs in 4 PHP files
- [ ] Update navigation paths in 3 HTML files
- [ ] Update app.js API_BASE constant
- [ ] Upload all files via SFTP/FTP
- [ ] Run `setup.php` to initialize DB
- [ ] Test homepage, link creation, redirects
- [ ] Enable HTTPS (usually free with hosting)

---

## ❓ Need Exact File Edits?

**Tell me your subdomain setup:**

Option 1: `https://yoursubdomain.com/` (root)
Option 2: `https://yourdomain.com/tinylink/` (subdirectory)

I'll generate **exact configuration files** tailored to your specific domain! 🎯

