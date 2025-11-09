#!/bin/bash

# TinyLink - Health Check Script
# Run this to verify everything is working correctly

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║           🩺 TinyLink Health Check Script                      ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo

# Check 1: Apache Running
echo "1️⃣  Checking Apache..."
if curl -s http://localhost/ > /dev/null 2>&1; then
    echo "   ✅ Apache is running"
else
    echo "   ❌ Apache is NOT running"
    echo "      Start with: sudo /Applications/XAMPP/xamppfiles/xampp start"
    exit 1
fi
echo

# Check 2: MySQL/Database
echo "2️⃣  Checking MySQL..."
if /Applications/XAMPP/bin/mysql -u root -e "SELECT 1" > /dev/null 2>&1; then
    echo "   ✅ MySQL is running"
else
    echo "   ❌ MySQL is NOT running"
    exit 1
fi
echo

# Check 3: Database Exists
echo "3️⃣  Checking database..."
if /Applications/XAMPP/bin/mysql -u root -e "USE tinylink_enhanced" > /dev/null 2>&1; then
    echo "   ✅ Database 'tinylink_enhanced' exists"
else
    echo "   ❌ Database 'tinylink_enhanced' does not exist"
    echo "      Create it by visiting: http://localhost/TinyLink/setup.php"
    exit 1
fi
echo

# Check 4: Database Tables
echo "4️⃣  Checking tables..."
TABLES=$(/Applications/XAMPP/bin/mysql -u root tinylink_enhanced -e "SHOW TABLES;" 2>/dev/null | grep -E "users|urls|analytics|tiers" | wc -l)
if [ "$TABLES" -eq 4 ]; then
    echo "   ✅ All 4 tables exist (users, urls, analytics, tiers)"
else
    echo "   ❌ Missing tables! Found $TABLES of 4"
    echo "      Recreate by visiting: http://localhost/TinyLink/setup.php"
    exit 1
fi
echo

# Check 5: API Files Exist
echo "5️⃣  Checking API files..."
API_FILES=(
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/api/shorten.php"
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/api/auth.php"
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/api/analytics.php"
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/api/qrcode.php"
)

MISSING=0
for file in "${API_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "   ❌ Missing: $file"
        MISSING=$((MISSING + 1))
    fi
done

if [ $MISSING -eq 0 ]; then
    echo "   ✅ All API files present"
else
    echo "   ❌ $MISSING API files missing"
    exit 1
fi
echo

# Check 6: Test Shorten API
echo "6️⃣  Testing Shorten API..."
RESPONSE=$(curl -s -X POST 'http://localhost/TinyLink/api/shorten.php' \
    -H 'Content-Type: application/json' \
    -d '{"url":"https://www.example.com"}')

if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Shorten API working"
else
    echo "   ❌ Shorten API failed"
    echo "   Response: $RESPONSE"
    exit 1
fi
echo

# Check 7: Test Auth API
echo "7️⃣  Testing Auth API..."
RESPONSE=$(curl -s -X POST 'http://localhost/TinyLink/api/auth.php?action=register' \
    -H 'Content-Type: application/json' \
    -d "{\"email\":\"healthcheck-$(date +%s)@example.com\",\"password\":\"Test12345\",\"username\":\"user-$(date +%s)\"}")

if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Auth API working"
else
    echo "   ❌ Auth API failed"
    echo "   Response: $RESPONSE"
fi
echo

# Check 8: Configuration Files
echo "8️⃣  Checking configuration..."
if grep -q "tinylink_enhanced" /Applications/XAMPP/xamppfiles/htdocs/TinyLink/config/db.php; then
    echo "   ✅ Database config correct"
else
    echo "   ❌ Database config incorrect"
    echo "      Expected: tinylink_enhanced"
    exit 1
fi
echo

# Check 9: Frontend Files
echo "9️⃣  Checking frontend files..."
FRONTEND_FILES=(
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/index.html"
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/login.html"
    "/Applications/XAMPP/xamppfiles/htdocs/TinyLink/dashboard.html"
)

MISSING=0
for file in "${FRONTEND_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "   ❌ Missing: $file"
        MISSING=$((MISSING + 1))
    fi
done

if [ $MISSING -eq 0 ]; then
    echo "   ✅ All frontend files present"
else
    echo "   ❌ $MISSING frontend files missing"
fi
echo

# Summary
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                    ✅ ALL CHECKS PASSED!                       ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo
echo "🚀 You're ready to go!"
echo
echo "Next steps:"
echo "  1. Open: http://localhost/TinyLink/index.html"
echo "  2. Create a shortened URL"
echo "  3. Register at: http://localhost/TinyLink/login.html"
echo "  4. View dashboard: http://localhost/TinyLink/dashboard.html"
echo
