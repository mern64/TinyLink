# TinyLink - Environment Configuration

This directory contains environment-specific configurations.

## Files in This Directory

### `db.php` (Current - Production Ready)
- **What it does:** Connects your application to the database
- **When to edit:** After deploying to hosting
- **What to change:** 
  - Database host, username, password
  - Database name
  
### `config.example.php` (Template)
- **What it does:** Example configuration template
- **How to use:** Copy to create environment-specific configs
- **Never commit:** Real credentials to version control

---

## 🚀 For Production Deployment

### Quick Setup

1. **Get your hosting database details:**
   - Database host (usually `localhost`)
   - Database username
   - Database password
   - Database name

2. **Edit `config/db.php`:**
   ```php
   $host = 'your-db-host';      // Your hosting provider's DB server
   $username = 'your-db-user';  // Your database username
   $password = 'your-db-pass';  // Your database password
   $dbname = 'your-db-name';    // Your database name
   ```

3. **Upload to hosting** via SFTP/FTP

4. **Initialize database** by visiting:
   - `https://yoursubdomain.com/setup.php`

---

## 🔒 Security Best Practices

- ✅ **Never commit** `db.php` to public repositories
- ✅ **Use strong passwords** for database
- ✅ **Check file permissions:** 644 for PHP files
- ✅ **Protect setup script** after initialization:
  ```apache
  <FilesMatch "setup.php">
      Order allow,deny
      Deny from all
  </FilesMatch>
  ```

---

## 📝 Development vs Production

**Local Development (XAMPP):**
```php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'tinylink_enhanced';
```

**Production (Shared Hosting):**
```php
$host = 'localhost';  // Check with your host
$username = 'user_12345';
$password = 'secure_password_here';
$dbname = 'user_tinylink_prod';
```

---

## 🆘 Troubleshooting

**"Database connection failed"**
- Verify credentials in `db.php`
- Check database exists on hosting
- Confirm database user has proper permissions
- Run setup script again

**"Can't connect to server"**
- Confirm database host (ask hosting provider)
- Check username/password
- Verify database name spelling

---

**Need help?** Check `HOSTING_SETUP_GUIDE.md` for complete instructions!

