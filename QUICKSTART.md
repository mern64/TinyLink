# TinyLink - Quick Start Guide 🚀

## Installation in 3 Steps

### Step 1: Start XAMPP
```bash
sudo /Applications/XAMPP/xamppfiles/xampp start
```

### Step 2: Initialize Database
Open in your browser:
```
http://localhost/TinyLink/setup.php
```
Wait for confirmation that the database was created successfully.

### Step 3: Launch Application
Open in your browser:
```
http://localhost/TinyLink/
```

## Usage

1. **Paste a URL** - Enter any long URL in the form
2. **Click "Shorten URL"** - Backend generates a unique short code
3. **Copy & Share** - Click the "Copy" button to copy the short link
4. **Track Clicks** - The database automatically counts clicks

## File Overview

```
TinyLink/
│
├── 🗂️ CONFIG
│   └── db.php              → Database connection settings
│
├── 🔌 API
│   └── shorten.php         → Core shortening logic & Base62 generation
│
├── 🖥️ FRONTEND
│   ├── index.php           → Main UI page
│   ├── redirect.php        → Click tracking & redirection
│   ├── setup.php           → Database initialization
│   │
│   └── assets/
│       ├── css/style.css   → Modern styling & animations
│       └── js/app.js       → Form handling & API calls
│
├── ⚙️ CONFIG
│   └── .htaccess           → URL routing (optional)
│
└── 📖 Documentation
    ├── README.md           → Full documentation
    └── QUICKSTART.md       → This file
```

## Key Features Implemented ✅

### Core Functions
- ✅ URL Shortening with unique code generation
- ✅ Database storage with MySQL
- ✅ Instant link redirection
- ✅ RESTful API (POST /api/shorten.php)

### Enhanced Functions
- ✅ Click tracking (click_count increments automatically)
- ✅ Base62 unique ID generation
- ✅ Timestamp tracking (created_at, last_accessed)
- ✅ Collision detection and prevention

### Frontend Features
- ✅ Modern, responsive UI
- ✅ Real-time form validation
- ✅ Loading/error/success states
- ✅ One-click clipboard copying
- ✅ Mobile-friendly design

## Technical Details

### URL Shortening Algorithm
1. Generate random 6-character code using Base62 (0-9, a-z, A-Z)
2. Check if code already exists in database
3. If unique, save mapping to database
4. If collision, retry automatically
5. Return short URL to frontend

### API Endpoint
```
POST /api/shorten.php

Request:  { "url": "https://example.com/long/url" }
Response: { 
    "success": true, 
    "short_url": "http://localhost/TinyLink/abc123",
    "short_code": "abc123"
}
```

### Database Structure
```
Table: urls
├── id (INT, Primary Key)
├── long_url (LONGTEXT)
├── short_code (VARCHAR, UNIQUE)
├── click_count (INT)
├── created_at (TIMESTAMP)
└── last_accessed (TIMESTAMP)
```

## Customization

### Change Short Code Length
Edit: `api/shorten.php` (line ~50)
```php
$codeLength = 6;  // Change to 5, 7, 8, etc.
```

### Change Database Name
Edit: `config/db.php` and `setup.php`
```php
$dbname = 'tinylink';  // Change to your preferred name
```

### Change Base URL
Edit: `api/shorten.php` (line ~85)
```php
'short_url' => 'http://localhost/TinyLink/' . $short_code
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Database connection failed | Start XAMPP MySQL, check credentials |
| 404 Link Not Found | Database not initialized, run setup.php |
| API returns error | Check browser console, verify URL format |
| Copy button doesn't work | Your browser may not support clipboard API |

## Performance Optimization Tips

1. **Database Indexes**: Already implemented on `short_code` and `created_at`
2. **Short Codes**: Auto-expands length if too many collisions
3. **Prepared Statements**: All SQL queries use bound parameters
4. **Error Handling**: Graceful error messages with proper HTTP status codes

## Security Features

✅ SQL injection prevention (prepared statements)
✅ URL validation (FILTER_VALIDATE_URL)
✅ XSS protection (htmlspecialchars in error display)
✅ Collision detection (prevents duplicate short codes)
✅ Input sanitization

## What's Next?

After setup, you can enhance TinyLink with:
- User accounts & authentication
- Custom short codes
- QR code generation
- Analytics dashboard
- Link expiration
- Password protection

---

**Questions?** Review the detailed `README.md` file or check the inline code comments.

**Enjoy!** 🎉
