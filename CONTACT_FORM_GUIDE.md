# 📬 TinyLink Contact Form - Complete Documentation

## Quick Overview

A complete, secure contact form system for collecting visitor messages. All data stored in database with automatic spam protection (5 messages per IP per hour max).

**Includes:**
- Professional contact form UI on homepage
- Full database integration
- Client & server-side validation
- XSS & SQL injection prevention
- Rate limiting & spam protection
- Admin API for retrieving messages

---

## 📁 Files Changed

| File | Change | Purpose |
|------|--------|---------|
| `api/contact.php` | ✨ NEW | Form handler & message API |
| `index.html` | ✏️ UPDATED | Added contact form UI |
| `setup.php` | ✏️ UPDATED | Added database table |

---

## 🗄️ Database Table

**Table:** `contact_messages`

```

| Field | Type | Size | Required | Notes |
|-------|------|------|----------|-------|
| id | INT | - | ✓ | Primary key, auto-increment |
| name | VARCHAR | 100 | ✓ | Visitor name (2-100 chars) |
| email | VARCHAR | 255 | ✓ | Valid email format |
| subject | VARCHAR | 200 | ✓ | Message subject (5-200 chars) |
| message | LONGTEXT | - | ✓ | Message content (10-5000 chars) |
| ip_address | VARCHAR | 45 | - | Visitor IP for tracking |
| status | ENUM | - | ✓ | new/read/replied/closed |
| created_at | TIMESTAMP | - | - | Auto-set on insert |
| updated_at | TIMESTAMP | - | - | Auto-update on change |

**Indexes:** id (PRIMARY), idx_email, idx_status, idx_created_at

---

## 🎯 Form Field Validation

| Field | Min | Max | Type | Validation |
|-------|-----|-----|------|-----------|
| Name | 2 | 100 | Text | Length check |
| Email | - | 255 | Email | RFC format |
| Subject | 5 | 200 | Text | Length check |
| Message | 10 | 5000 | Textarea | Length check |

---

## 💻 API Endpoints

### Submit Form (POST)

```
POST /api/contact.php
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Question about service",
  "message": "I have a question about your service..."
}
```

**Success (201 Created):**
```json
{
  "success": true,
  "message": "Thank you! Your message has been received...",
  "message_id": 42
}
```

**Validation Error (400 Bad Request):**
```json
{
  "success": false,
  "errors": ["Name must be between 2 and 100 characters", "Invalid email"]
}
```

**Rate Limited (429 Too Many Requests):**
```json
{
  "success": false,
  "message": "Too many messages from this IP. Please try again later."
}
```

---

### Get Messages (GET)

```
GET /api/contact.php?action=getMessages
```

**Response (200 OK):**
```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "subject": "Question",
      "message": "I have a question...",
      "created_at": "2025-11-09 14:30:00",
      "status": "new"
    }
  ]
}
```

**HTTP Codes:**
- 201: Message created
- 400: Validation error
- 429: Rate limit exceeded
- 500: Server error

---

## 🚀 Quick Start

**Step 1: Initialize Database**
```
Visit: http://localhost/TinyLink/setup.php
Look for: ✓ Table 'contact_messages' created successfully
```

**Step 2: Test Form**
```
Visit: http://localhost/TinyLink/index.html
Scroll to: "Get in Touch" section
Fill form & click: "Send Message"
Expected: Green success message
```

**Step 3: Verify in Database**
```sql
SELECT * FROM contact_messages ORDER BY created_at DESC;
```

**Step 4: Retrieve Messages**
```javascript
// Browser console:
fetch('/TinyLink/api/contact.php?action=getMessages')
  .then(r => r.json())
  .then(data => console.table(data.messages))
```

---

## 🔐 Security Features

✅ **SQL Injection Prevention** - Prepared statements  
✅ **XSS Prevention** - HTML entity encoding (htmlspecialchars)  
✅ **Rate Limiting** - Max 5 messages/IP/hour  
✅ **Input Validation** - Client & server-side checks  
✅ **Email Validation** - RFC-compliant format checking  
✅ **CORS Support** - Safe cross-origin requests  
✅ **Data Sanitization** - All input cleaned before storage  

---

## 🧪 Testing Checklist

### Database Setup
- [ ] Database initializes without errors
- [ ] `contact_messages` table created
- [ ] All fields exist with correct types

### Form UI/UX
- [ ] Form visible on homepage
- [ ] All fields display correctly
- [ ] Responsive on mobile (dev tools)
- [ ] Responsive on tablet
- [ ] Responsive on desktop

### Valid Submission
```
Input:
  Name: John Doe
  Email: john@example.com
  Subject: Test Subject
  Message: This is a test message

Expected:
  ✓ Success message displays
  ✓ Message in database
  ✓ Status = 'new'
  ✓ Form clears
```

### Name Validation
```
Too short (1 char):        Error shown, not saved
Too long (101+ chars):     Error shown, not saved
Valid (2-100 chars):       Accepted ✓
```

### Email Validation
```
Invalid (notanemail):      Error shown
Valid (user@example.com):  Accepted ✓
Valid variations:
  - test.name@domain.co.uk ✓
  - admin+tag@company.org ✓
```

### Subject Validation
```
Too short (4 chars):       Error shown
Too long (201+ chars):     Error shown
Valid (5-200 chars):       Accepted ✓
```

### Message Validation
```
Too short (9 chars):       Error shown
Too long (5001+ chars):    Error shown
Valid (10-5000 chars):     Accepted ✓
```

### Security Tests
```
XSS in name: <script>alert('xss')</script>
  Result: Escaped & safe ✓

SQL injection: '; DROP TABLE x; --
  Result: Treated as text, table safe ✓

Rate limit (6th message in 1 hour):
  Result: Blocked with 429 error ✓
```

### Database Operations
```
Query all:     SELECT * FROM contact_messages;
Query new:     SELECT * WHERE status = 'new';
Update status: UPDATE ... SET status = 'read';
Delete:        DELETE FROM contact_messages WHERE id = 1;
```

### Cross-Browser
- [ ] Chrome ✓
- [ ] Firefox ✓
- [ ] Safari ✓
- [ ] Edge ✓
- [ ] Mobile browsers ✓

### Accessibility
- [ ] Form labels associated with inputs
- [ ] Keyboard navigation works
- [ ] Error messages visible to screen readers
- [ ] Good color contrast

---

## 💾 Common Database Queries

**View all messages:**
```sql
SELECT * FROM contact_messages ORDER BY created_at DESC;
```

**View unread messages:**
```sql
SELECT * FROM contact_messages WHERE status = 'new';
```

**View today's messages:**
```sql
SELECT * FROM contact_messages WHERE DATE(created_at) = CURDATE();
```

**Count by status:**
```sql
SELECT status, COUNT(*) as count FROM contact_messages GROUP BY status;
```

**Mark as read:**
```sql
UPDATE contact_messages SET status = 'read' WHERE id = 1;
```

**Mark as replied:**
```sql
UPDATE contact_messages SET status = 'replied' WHERE id = 1;
```

**Delete message:**
```sql
DELETE FROM contact_messages WHERE id = 1;
```

**Search messages:**
```sql
SELECT * FROM contact_messages WHERE message LIKE '%keyword%';
```

---

## 🛠️ Usage Guide

### For Visitors
1. Scroll to "Get in Touch" section on homepage
2. Fill all form fields (all required)
3. Click "Send Message"
4. See success confirmation

### For Developers - Retrieve Messages

**JavaScript:**
```javascript
const getMessages = async () => {
  const response = await fetch('/TinyLink/api/contact.php?action=getMessages');
  const data = await response.json();
  console.table(data.messages);
};
```

**cURL:**
```bash
curl "http://localhost/TinyLink/api/contact.php?action=getMessages"
```

**PHP:**
```php
$response = file_get_contents('http://localhost/TinyLink/api/contact.php?action=getMessages');
$data = json_decode($response, true);
print_r($data['messages']);
```

**MySQL:**
```bash
mysql -u root -p tinylink_enhanced
SELECT * FROM contact_messages;
```

---

## 🔧 Customization

### Change Rate Limit
File: `api/contact.php`, Line ~50

```php
// From: 5 messages per hour
if ($spam_result['count'] >= 5) {

// To: 10 messages per hour
if ($spam_result['count'] >= 10) {
```

### Change Field Lengths
File: `api/contact.php`, Lines ~30-40

```php
// Current: Name 2-100 chars, modify as needed:
strlen($name) < 2 || strlen($name) > 100
strlen($subject) < 5 || strlen($subject) > 200
strlen($message) < 10 || strlen($message) > 5000
```

### Modify Form Fields
File: `index.html`, Around line 370

1. Add/remove HTML input field
2. Update JavaScript validation
3. Update API validation in `api/contact.php`

---

## 💡 Optional Enhancements

**1. Email Notifications**
```php
// In api/contact.php after saving:
mail('admin@example.com', 'New Message from ' . $name, $message);
```

**2. Admin Dashboard**
Create `/admin/messages.php` to view, search, and manage messages

**3. Auto-Reply Email**
```php
mail($email, 'We Received Your Message', 
     'Thank you for contacting us. We will respond shortly.');
```

**4. Message Categories**
Add `category` column to track Support, Sales, General, etc.

**5. File Attachments**
Allow uploads to `/uploads/` directory with `attachment_path` column

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection failed | Check credentials in `config/db.php` |
| Form not appearing | Verify table created, check browser console |
| Messages not saving | Check table exists, verify INSERT permissions |
| Rate limiting too strict | Edit `api/contact.php` rate limit threshold |
| API returns 500 error | Check database connection, review error logs |

---

## ✅ Pre-Deployment Checklist

Before deploying to production:

- [ ] Database credentials updated in `config/db.php`
- [ ] API paths updated in HTML (from `/TinyLink/` to `/`)
- [ ] Setup script run on production
- [ ] Contact form tested on production
- [ ] Messages save to database
- [ ] Rate limiting works
- [ ] Validation errors display correctly
- [ ] Security tests passed (XSS, SQL injection)
- [ ] HTTPS enabled (recommended)
- [ ] File permissions set (644)

---

## 📊 Production Setup

For **tinylink.dramran.com**:

**1. Update Database** (`config/db.php`)
```php
$host = 'localhost';
$username = 'dramranc_tinylink';
$password = 'mhD$GLplMDnn';
$dbname = 'dramranc_tinylink';
```

**2. Update API Paths** (`index.html`)
```javascript
// From: fetch('/TinyLink/api/contact.php', ...)
// To:   fetch('/api/contact.php', ...)
```

**3. Initialize Database**
```
Visit: https://tinylink.dramran.com/setup.php
```

**4. Remove Setup File (Optional)**
```bash
rm setup.php
# or: mv setup.php setup.php.bak
```

---

## 📞 Support

If you encounter issues:

1. Check this documentation
2. Review browser console errors
3. Check server error logs
4. Verify database table structure
5. Test with provided test cases

---

**Contact Form Ready for Production!** ✅
