# TinyLink - Clone & Fork Guide for Friends 🔗

A complete guide for your friends to clone or fork TinyLink and test it on their PC.

---

## 📋 Prerequisites

Before getting started, make sure you have:

- **Git** installed ([download here](https://git-scm.com/download))
- **XAMPP** or similar (Apache + MySQL + PHP) ([download here](https://www.apachefriends.org/))
- **Web browser** (Chrome, Firefox, Safari, Edge)
- **Terminal/Command Prompt** access

### Verify Installation

```bash
# Check Git
git --version

# Check PHP (if XAMPP installed)
php --version

# Check MySQL
mysql --version
```

---

## 🍴 Option 1: Fork & Clone (Recommended)

**Best for:** Contributing to the project or maintaining your own version

### Step 1: Fork on GitHub
1. Go to: [TinyLink Repository](https://github.com/username/TinyLink)
2. Click the **"Fork"** button (top right)
3. Select where to fork (your account)
4. Wait for the fork to complete

### Step 2: Clone Your Fork
```bash
# Replace YOUR_USERNAME with your GitHub username
git clone https://github.com/YOUR_USERNAME/TinyLink.git

# Navigate to project
cd TinyLink
```

### Step 3: Add Upstream Remote (Optional)
This allows you to sync with the original repository:
```bash
git remote add upstream https://github.com/ORIGINAL_USERNAME/TinyLink.git

# Verify remotes
git remote -v
# Should show:
# origin    - Your fork
# upstream  - Original repository
```

---

## 📥 Option 2: Direct Clone

**Best for:** Just testing the project without contributing

```bash
# Clone the repository
git clone https://github.com/ORIGINAL_USERNAME/TinyLink.git

# Navigate to project
cd TinyLink
```

---

## 🔧 Setup on Your Computer (macOS/Linux)

### Step 1: Verify XAMPP Installation
```bash
# Check if XAMPP is installed
ls /Applications/XAMPP/
# or on Linux: ls /opt/lampp/
```

### Step 2: Copy Project to XAMPP
```bash
# For macOS
cp -r TinyLink /Applications/XAMPP/xamppfiles/htdocs/

# For Linux
sudo cp -r TinyLink /opt/lampp/htdocs/
```

### Step 3: Start XAMPP Services
```bash
# macOS
sudo /Applications/XAMPP/xamppfiles/xampp start

# Linux
sudo /opt/lampp/lampp start

# Windows (use XAMPP Control Panel GUI)
# Or Command Line:
cd "C:\xampp"
xampp_start.exe
```

### Step 4: Initialize Database
Open in your browser:
```
http://localhost/TinyLink/setup.php
```

**Expected output:**
```
✓ Database 'tinylink' created or already exists
✓ Table 'urls' created successfully
```

### Step 5: Test Application
Open in browser:
```
http://localhost/TinyLink/
```

**You should see:**
- TinyLink logo with tagline
- URL shortening form
- Features section

---

## 🪟 Setup on Windows

### Step 1: Install XAMPP
1. Download from [apachefriends.org](https://www.apachefriends.org/)
2. Run installer
3. Choose installation path (default: `C:\xampp`)
4. Install Apache, MySQL, PHP

### Step 2: Copy Project
```bash
# Copy TinyLink to htdocs
Copy TinyLink folder to: C:\xampp\htdocs\
```

Or use File Explorer:
1. Open `C:\xampp\htdocs\`
2. Paste TinyLink folder here

### Step 3: Start XAMPP
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Wait for both to show running

### Step 4: Initialize Database
1. Open browser
2. Go to: `http://localhost/TinyLink/setup.php`
3. See success message

### Step 5: Access TinyLink
1. Open browser
2. Go to: `http://localhost/TinyLink/`

---

## 📖 First Time Testing

### Test 1: Shorten a URL
1. Open `http://localhost/TinyLink/`
2. Paste: `https://www.google.com`
3. Click "Shorten URL"
4. You should get a short URL like `http://localhost/TinyLink/abc123`

### Test 2: Copy Short URL
1. Click the "Copy" button
2. Paste somewhere (Ctrl+V or Cmd+V)
3. You should see the short URL

### Test 3: Test Redirect
1. Copy the short URL
2. Open new browser tab
3. Paste and press Enter
4. You should be redirected to Google

### Test 4: Verify Click Tracking
1. Open phpMyAdmin: `http://localhost/phpmyadmin/`
2. Click database "tinylink"
3. Click table "urls"
4. Look at your short code row
5. `click_count` should show 1 (from the redirect test)

---

## 🔄 Syncing with Original (Forked Only)

### Pull Latest Changes
```bash
# Fetch from original
git fetch upstream

# Merge into your main branch
git merge upstream/main

# Push to your fork
git push origin main
```

Or use a simpler approach:
```bash
# Pull changes directly from upstream
git pull upstream main
```

---

## 🐛 Troubleshooting

### "Port 80 already in use"
```bash
# Find process using port 80
sudo lsof -i :80

# Kill the process (replace PID)
sudo kill -9 PID

# Or use different port in XAMPP config
```

### "Cannot connect to MySQL"
```bash
# Start XAMPP MySQL
sudo /Applications/XAMPP/xamppfiles/xampp start

# Or check if already running
sudo /Applications/XAMPP/xamppfiles/xampp status
```

### "404 Not Found"
- Verify TinyLink folder is in XAMPP htdocs
- Check URL: `http://localhost/TinyLink/` (case-sensitive on Linux)
- Clear browser cache: Ctrl+Shift+Delete

### "Database connection failed"
1. Run setup.php again: `http://localhost/TinyLink/setup.php`
2. Check database credentials in `config/db.php`
3. Verify MySQL is running

### "PHP code showing as text"
- Apache is not running
- Start XAMPP: `sudo /Applications/XAMPP/xamppfiles/xampp start`
- Wait a few seconds and refresh

---

## 📝 Making Changes

### If You Forked (Contributing)

```bash
# Create a feature branch
git checkout -b feature/your-feature-name

# Make changes
# Test on your XAMPP

# Stage changes
git add .

# Commit changes
git commit -m "Add: feature description"

# Push to your fork
git push origin feature/your-feature-name

# Create Pull Request on GitHub
```

### If You Cloned (Just Testing)

No need to worry about commits - just experiment!

But if you want to keep track:
```bash
# Initialize git if not already
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit"
```

---

## 🚀 Next Steps

### To Learn TinyLink
1. Read `README.md` for full documentation
2. Check `QUICKSTART.md` for quick reference
3. Review `PROJECT_SUMMARY.md` for architecture
4. Look at `CONFIGURATION.md` for customization

### To Contribute
1. Read `CONTRIBUTING.md` for guidelines
2. Create feature branch
3. Make improvements
4. Submit Pull Request

### To Deploy
1. Update `config/db.php` with production credentials
2. Change base URL in `api/shorten.php`
3. Deploy to web server
4. Configure SSL/HTTPS

---

## 💡 Tips for Success

### Development Tips
- Use browser DevTools (F12) to debug JavaScript
- Check browser console for errors
- Use phpMyAdmin to verify database operations
- Test on multiple browsers

### GitHub Tips
- Create descriptive branch names
- Write clear commit messages
- Keep commits focused and small
- Reference issues in commits

### Collaboration Tips
- Communicate with team before major changes
- Review others' pull requests
- Ask questions in GitHub Discussions
- Share knowledge with teammates

---

## 🆘 Getting Help

### Documentation
- **README.md** - Complete project documentation
- **QUICKSTART.md** - Quick reference guide
- **INSTALLATION_GUIDE.md** - Detailed setup
- **CONTRIBUTING.md** - Contribution guidelines

### Online Resources
- **Git Help**: `git help <command>`
- **PHP Documentation**: https://php.net/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **GitHub Help**: https://docs.github.com/

### Contact
- Ask in GitHub Discussions
- Check GitHub Issues
- Email the maintainer

---

## 📊 Project Structure Reference

```
TinyLink/
├── index.php               - Main application
├── redirect.php            - Link redirection
├── setup.php               - Database setup
├── api/
│   └── shorten.php         - API endpoint
├── config/
│   └── db.php              - Database config
├── assets/
│   ├── css/style.css       - Styling
│   └── js/app.js           - Frontend logic
└── [documentation files]   - Guides and docs
```

---

## ✅ Checklist for Friends

Before they start, make sure they have:

- [ ] Git installed
- [ ] XAMPP installed
- [ ] GitHub account (for forking)
- [ ] Terminal access
- [ ] Read this guide
- [ ] Cloned or forked the repository
- [ ] Copied to XAMPP htdocs
- [ ] Started XAMPP services
- [ ] Run setup.php
- [ ] Tested at `http://localhost/TinyLink/`

---

## 🎉 You're All Set!

Your friends can now:
- ✅ Clone the repository
- ✅ Setup on their computers
- ✅ Test TinyLink locally
- ✅ Make contributions
- ✅ Submit pull requests

---

**Questions?** Check the documentation files or GitHub Issues!

**Happy coding!** 🚀
