# TinyLink - Tier Testing Guide

## 📊 Test Accounts Created

All test accounts are ready to use with password: **Test12345**

### Account Details

| Tier | Email | Username | Link Limit | Status |
|------|-------|----------|-----------|--------|
| **Free** | `free@test.com` | test_free | 50 | ✅ |
| **Pro** | `pro@test.com` | test_pro | 500 | ✅ |
| **Enterprise** | `enterprise@test.com` | test_enterprise | 10,000+ | ✅ |

### Original Account (Still Available)

| Tier | Email | Username | Link Limit |
|------|-------|----------|-----------|
| **Free** | test@example.com | testuser | 50 |

---

## 🚀 Quick Start - Test Tiers Now

### Method 1: Browser Testing

1. **Open Login Page:**
   ```
   http://localhost/TinyLink/login.html
   ```

2. **Click "Already have an account? Sign in"**

3. **Choose a tier to test:**
   ```
   Email: free@test.com OR pro@test.com OR enterprise@test.com
   Password: Test12345
   ```

4. **Login and explore dashboard**

5. **Try creating links** - Notice different behaviors for each tier

---

## 🧪 CLI Testing - Test Tier Limits

### Test Free Tier (50 link limit)

```bash
# 1. Login as free user
FREE_TOKEN=$(curl -s -X POST 'http://localhost/TinyLink/api/auth.php?action=login' \
  -H 'Content-Type: application/json' \
  -d '{"email":"free@test.com","password":"Test12345"}' | jq -r '.token')

# 2. Create a link (should succeed)
curl -X POST 'http://localhost/TinyLink/api/shorten.php' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $FREE_TOKEN" \
  -d '{"url":"https://www.example.com/free"}' | jq .

# Expected response:
# {
#   "success": true,
#   "message": "URL shortened successfully",
#   "url_id": X,
#   "short_code": "XXXXX",
#   ...
# }
```

### Test Pro Tier (500 link limit)

```bash
# 1. Login as pro user
PRO_TOKEN=$(curl -s -X POST 'http://localhost/TinyLink/api/auth.php?action=login' \
  -H 'Content-Type: application/json' \
  -d '{"email":"pro@test.com","password":"Test12345"}' | jq -r '.token')

# 2. Create a link (should succeed)
curl -X POST 'http://localhost/TinyLink/api/shorten.php' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $PRO_TOKEN" \
  -d '{"url":"https://www.example.com/pro"}' | jq .

# Expected: Success (user_id will show as 5)
```

### Test Enterprise Tier (10,000+ link limit)

```bash
# 1. Login as enterprise user
ENT_TOKEN=$(curl -s -X POST 'http://localhost/TinyLink/api/auth.php?action=login' \
  -H 'Content-Type: application/json' \
  -d '{"email":"enterprise@test.com","password":"Test12345"}' | jq -r '.token')

# 2. Create a link (should succeed)
curl -X POST 'http://localhost/TinyLink/api/shorten.php' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $ENT_TOKEN" \
  -d '{"url":"https://www.example.com/enterprise"}' | jq .

# Expected: Success (user_id will show as 6)
```

---

## 📋 Feature Comparison by Tier

### Core Features (All Tiers)
- ✅ Create shortened URLs
- ✅ Track clicks
- ✅ Auto-generated short codes
- ✅ Custom aliases (when authenticated)
- ✅ QR code generation
- ✅ Basic analytics dashboard

### Free Tier Specific
- 📊 **Link Limit:** 50 links max
- ✅ Basic click tracking
- ✅ Device type detection
- ❌ Custom domain (Pro+)
- ❌ Advanced analytics (Pro+)
- ❌ Team features (Enterprise)

### Pro Tier Specific
- 📊 **Link Limit:** 500 links max
- ✅ All Free tier features
- ✅ Custom domain support (planned)
- ✅ Advanced analytics
- ✅ Referrer tracking
- ❌ Team features (Enterprise)

### Enterprise Tier Specific
- 📊 **Link Limit:** 10,000+ links (unlimited)
- ✅ All Pro tier features
- ✅ Team management (planned)
- ✅ Real-time analytics
- ✅ Priority support
- ✅ Custom integrations (planned)

---

## 🎯 Test Scenarios

### Scenario 1: Free Tier Limit Test

**Goal:** Verify that Free tier users cannot exceed 50 links

```bash
# Create a script to test limit
for i in {1..55}; do
  curl -s -X POST 'http://localhost/TinyLink/api/shorten.php' \
    -H 'Content-Type: application/json' \
    -H "Authorization: Bearer $FREE_TOKEN" \
    -d "{\"url\":\"https://example.com/link-$i\"}" | jq '.success'
done
```

**Expected Results:**
- First 50 requests: `"success": true`
- Request 51+: `"success": false` with message "Link limit reached"

### Scenario 2: Pro Tier Higher Limit

**Goal:** Verify Pro tier can create more links

Same as above but with `$PRO_TOKEN` - should succeed for 500+ links

### Scenario 3: Enterprise Unlimited

**Goal:** Verify Enterprise can create many links

Same as above but with `$ENT_TOKEN` - should always succeed

---

## 🔍 Database Verification

### View All Users

```bash
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "SELECT id, email, username, tier, links_limit, links_created FROM users ORDER BY tier;"
```

**Expected Output:**
```
id | email | username | tier | links_limit | links_created
1  | test@example.com | testuser | free | 50 | 0
4  | free@test.com | test_free | free | 50 | 0
5  | pro@test.com | test_pro | pro | 500 | 0
6  | enterprise@test.com | test_enterprise | enterprise | 10000 | 0
```

### View User's Links

```bash
# View free tier user's links
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "SELECT user_id, short_code, long_url, click_count FROM urls WHERE user_id = 4;"

# View pro tier user's links
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "SELECT user_id, short_code, long_url, click_count FROM urls WHERE user_id = 5;"

# View enterprise tier user's links
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "SELECT user_id, short_code, long_url, click_count FROM urls WHERE user_id = 6;"
```

### Update User Tier (Admin Testing)

```bash
# Promote free user to pro
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "UPDATE users SET tier = 'pro', links_limit = 500 WHERE id = 4;"

# Demote user back to free
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "UPDATE users SET tier = 'free', links_limit = 50 WHERE id = 4;"
```

---

## ✅ Verification Checklist

- [ ] Free tier login works
- [ ] Pro tier login works
- [ ] Enterprise tier login works
- [ ] Free tier can create links (up to 50)
- [ ] Pro tier can create links (up to 500)
- [ ] Enterprise tier can create links (unlimited)
- [ ] Tier limit enforcement works
- [ ] Links are associated with correct user
- [ ] Analytics track clicks per user
- [ ] Dashboard shows correct user info

---

## 🐛 Troubleshooting

### Can't Login

**Check database:**
```bash
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "SELECT email, password FROM users WHERE email LIKE '%test.com%';"
```

**Verify password hash:**
```bash
/Applications/XAMPP/bin/php << 'EOF'
<?php
$password = "Test12345";
$hash = '$2y$10$LjXHQTEPtJCujXT/yK3XM./c37vVUOlKIB8JiZx5uZhQSWWS1x59G';
if (password_verify($password, $hash)) {
    echo "✅ Password hash is correct\n";
} else {
    echo "❌ Password hash is incorrect\n";
}
?>
EOF
```

### Link Limit Not Enforcing

**Check user tier:**
```bash
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "SELECT id, email, tier, links_limit, links_created FROM users WHERE email = 'free@test.com';"
```

**Manually update if needed:**
```bash
/Applications/XAMPP/bin/mysql -u root tinylink_enhanced \
  -e "UPDATE users SET tier = 'free', links_limit = 50 WHERE email = 'free@test.com';"
```

### Links Not Showing in Dashboard

- Verify user_id is correct in urls table
- Check that links have created_at timestamp
- Verify user is logged in with correct token

---

## 💡 Advanced Testing

### Test Custom Aliases per Tier

```bash
# Free tier with custom alias
curl -X POST 'http://localhost/TinyLink/api/shorten.php' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $FREE_TOKEN" \
  -d '{"url":"https://example.com","alias":"free-link"}' | jq .

# Pro tier with custom alias
curl -X POST 'http://localhost/TinyLink/api/shorten.php' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $PRO_TOKEN" \
  -d '{"url":"https://example.com","alias":"pro-link"}' | jq .
```

### Test Analytics per Tier

```bash
# Get free tier analytics
curl -s -X GET 'http://localhost/TinyLink/api/analytics.php?action=dashboard' \
  -H "Authorization: Bearer $FREE_TOKEN" | jq .

# Get pro tier analytics
curl -s -X GET 'http://localhost/TinyLink/api/analytics.php?action=dashboard' \
  -H "Authorization: Bearer $PRO_TOKEN" | jq .
```

---

## 📈 Performance Testing Ideas

1. **Bulk Link Creation:**
   - Create 50 links for Free tier
   - Create 500 links for Pro tier
   - Monitor database performance

2. **Click Tracking:**
   - Generate many clicks
   - Verify analytics accuracy
   - Check query performance

3. **Concurrent Requests:**
   - Multiple simultaneous link creations
   - Test tier limit enforcement under load

---

## 🎓 Learning Resources

- **Tier System Logic:** See `api/shorten.php` lines 57-84
- **Database Schema:** See `setup-enhanced.php` users table
- **Authentication:** See `api/auth.php` JWT token generation
- **Testing Docs:** See this file for comprehensive testing

---

## 📞 Support

**Test Account Information:**
- All accounts use password: `Test12345`
- All accounts have bcrypt hashing
- Tokens expire after 30 days
- Links are associated with user_id

**More Help:**
- Check `DEBUGGING_FIX_SUMMARY.md` for troubleshooting
- Check `QUICK_START_AFTER_FIX.md` for quick reference
- Check `REDIRECT_FIX_SUMMARY.md` for redirect issues

---

**Ready to test? Open http://localhost/TinyLink/login.html and start testing tiers!** 🚀
