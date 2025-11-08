# TinyLink - URL Shortener 🔗

A full-stack URL shortening application demonstrating a complete web application with **PHP backend**, **MySQL database**, and **vanilla JavaScript frontend**.

## 🎯 Features

### 💻 Core Functions (MVP)
- **URL Shortening**: Generate unique short codes for long URLs
- **Database Storage**: Permanently store URL mappings in MySQL
- **Link Redirection**: Instant redirection to original URLs
- **RESTful API**: POST endpoint for URL shortening requests

### 📈 Enhanced Functions
- **Click Tracking**: Count and track every link click
- **Unique ID Generation**: Base62 algorithm ensures collision-free short codes
- **Timestamps**: Track creation time and last access time
- **Analytics Ready**: Built-in database structure for future analytics features

## 📁 Project Structure

```
TinyLink/
├── config/
│   └── db.php                 # Database configuration and connection
├── api/
│   └── shorten.php           # API endpoint for URL shortening
├── assets/
│   ├── css/
│   │   └── style.css         # Modern, responsive styling
│   └── js/
│       └── app.js            # Frontend application logic
├── index.php                  # Main application page
├── redirect.php              # Redirect handler with click tracking
├── setup.php                 # Database setup script
└── README.md                 # This file
```

## 🚀 Getting Started

### Prerequisites
- XAMPP (or any PHP + MySQL server)
- Modern web browser
- Basic knowledge of PHP and MySQL

### Installation Steps

1. **Place files in XAMPP**
   - Files should be in: `/Applications/XAMPP/xamppfiles/htdocs/TinyLink/`

2. **Start XAMPP Services**
   ```bash
   # Start Apache and MySQL
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

3. **Initialize Database**
   - Open browser: `http://localhost/TinyLink/setup.php`
   - Follow the on-screen setup wizard
   - The database and table will be created automatically

4. **Access the Application**
   - Open: `http://localhost/TinyLink/`
   - Start shortening URLs!

## 📖 How It Works

### URL Shortening Flow

```
1. User enters long URL in the form
   ↓
2. JavaScript validates the URL format
   ↓
3. POST request sent to api/shorten.php
   ↓
4. Backend generates unique short code (Base62)
   ↓
5. URL mapping stored in MySQL database
   ↓
6. Short URL returned to frontend
   ↓
7. User can copy and share the short link
```

### Link Redirection & Click Tracking Flow

```
1. User clicks short link: /redirect.php?code=abc123
   ↓
2. Backend looks up short code in database
   ↓
3. Click count incremented automatically
   ↓
4. Timestamp updated (last_accessed)
   ↓
5. User redirected to original URL
```

## 🗄️ Database Schema

### urls table
```sql
CREATE TABLE urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    long_url LONGTEXT NOT NULL,
    short_code VARCHAR(10) NOT NULL UNIQUE,
    click_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_accessed TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_short_code (short_code),
    INDEX idx_created_at (created_at)
)
```

**Fields:**
- `id`: Auto-incrementing primary key
- `long_url`: Original URL (stored as LONGTEXT for long URLs)
- `short_code`: Unique 6-character Base62 code
- `click_count`: Number of times the link has been accessed
- `created_at`: Timestamp when URL was shortened
- `last_accessed`: Timestamp of most recent click

## 🔌 API Documentation

### POST /api/shorten.php

**Request:**
```json
{
    "url": "https://example.com/very/long/url/path"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "URL shortened successfully",
    "short_code": "abc123",
    "short_url": "http://localhost/TinyLink/abc123",
    "original_url": "https://example.com/very/long/url/path"
}
```

**Error Response (400/500):**
```json
{
    "success": false,
    "message": "Error description"
}
```

## 🎨 Frontend Features

- **Modern UI**: Gradient background with smooth animations
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Form Validation**: Client-side and server-side validation
- **Loading States**: Visual feedback during API calls
- **Error Handling**: User-friendly error messages
- **Copy to Clipboard**: One-click copying of short URLs
- **Feature Showcase**: How it works section and feature cards

## ⚙️ Technical Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | PHP 7+ |
| Database | MySQL 5.7+ |
| Server | Apache (XAMPP) |

## 🔐 Security Features

- URL validation (FILTER_VALIDATE_URL)
- SQL injection prevention (Prepared statements with bound parameters)
- Error suppression in production
- CSRF protection ready (can be added)
- Input sanitization

## 📊 Code Highlights

### Unique Code Generation (Base62)
The application uses a Base62 encoding algorithm to generate short codes:
- **Character set**: 0-9, a-z, A-Z (62 characters)
- **Default length**: 6 characters
- **Collision detection**: Checks database before confirming code
- **Auto-expansion**: If collisions occur, length automatically increases

### Click Tracking
```php
UPDATE urls SET 
    click_count = click_count + 1, 
    last_accessed = NOW() 
WHERE id = ?
```

## 🛠️ Customization

### Change Short Code Length
Edit `api/shorten.php`, line ~50:
```php
$codeLength = 6; // Change this value
```

### Modify Database Credentials
Edit `config/db.php`:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'tinylink';
```

### Change Base URL
The short URLs default to `http://localhost/TinyLink/`
Edit in `api/shorten.php` line ~85:
```php
'short_url' => 'http://yourdomain.com/TinyLink/' . $short_code
```

## 🐛 Troubleshooting

### "Database connection failed"
- Ensure XAMPP MySQL is running
- Check database credentials in `config/db.php`
- Verify MySQL user and password

### "404 - Link Not Found"
- Short code doesn't exist in database
- Database may not be initialized
- Run `setup.php` again

### Duplicate short code error
- Very rare, but can happen with collisions
- Application automatically handles this by checking for duplicates
- Try refreshing the page

### CORS Issues
- Only relevant if frontend and backend are on different domains
- Add CORS headers to `api/shorten.php` if needed

## 📈 Future Enhancements

- [ ] User authentication and URL management
- [ ] Custom short codes
- [ ] QR code generation
- [ ] Advanced analytics dashboard
- [ ] Expiration dates for links
- [ ] Password protection
- [ ] Link categories/tags
- [ ] Bulk URL shortening

## 📝 License

Free to use for educational and personal projects.

## 👨‍💻 Author

Created as a demonstration of full-stack web development with PHP, MySQL, and Vanilla JavaScript.

---

**Need Help?** Check the setup.php page or review the code comments for detailed explanations.

**Happy Link Shortening!** 🚀
