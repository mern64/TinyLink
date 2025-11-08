# Contributing to TinyLink

Thank you for your interest in contributing to TinyLink! This document provides guidelines and instructions for developers who want to contribute.

## 🚀 Getting Started

### Prerequisites
- **XAMPP** installed with PHP 7+ and MySQL
- **Git** installed on your system
- A **GitHub account**
- Basic knowledge of PHP, JavaScript, and MySQL

### Fork & Clone the Repository

#### Option 1: Fork (Recommended for contributions)
```bash
# 1. Click "Fork" on GitHub
# 2. Clone your fork
git clone https://github.com/YOUR_USERNAME/TinyLink.git
cd TinyLink
```

#### Option 2: Direct Clone (If collaborator)
```bash
# Clone the repository
git clone https://github.com/ORIGINAL_USERNAME/TinyLink.git
cd TinyLink
```

## 🔧 Local Development Setup

### Step 1: Place Files in XAMPP
```bash
# Copy to XAMPP htdocs
cp -r TinyLink /Applications/XAMPP/xamppfiles/htdocs/
```

### Step 2: Start XAMPP Services
```bash
sudo /Applications/XAMPP/xamppfiles/xampp start
```

### Step 3: Initialize Database
Open in browser:
```
http://localhost/TinyLink/setup.php
```

### Step 4: Test Installation
```bash
# Navigate to project
cd /Applications/XAMPP/xamppfiles/htdocs/TinyLink

# Check structure
ls -la
```

## 📝 Development Workflow

### Creating a Branch
```bash
# Create a feature branch
git checkout -b feature/your-feature-name

# Or bugfix branch
git checkout -b bugfix/issue-description
```

### Making Changes
1. Make your changes
2. Test thoroughly on your local XAMPP
3. Follow code style (see below)

### Committing Changes
```bash
# Stage changes
git add .

# Commit with clear message
git commit -m "Add: feature description"
# or
git commit -m "Fix: bug description"
# or
git commit -m "Docs: documentation update"
```

### Pushing Changes
```bash
# Push to your fork
git push origin feature/your-feature-name
```

### Creating a Pull Request
1. Go to GitHub
2. Click "Compare & pull request"
3. Write clear PR description
4. Submit for review

## 📋 Code Style Guidelines

### PHP
```php
// Use meaningful variable names
$short_code = generateCode();

// Add comments for complex logic
// Generate unique 6-character code using Base62
$short_code = generateUniqueCode($conn);

// Use prepared statements for security
$stmt = $conn->prepare("SELECT * FROM urls WHERE short_code = ?");
```

### JavaScript
```javascript
// Use camelCase for variables
const formElement = document.getElementById('shortenForm');

// Use descriptive function names
function validateURL(url) {
    // validation logic
}

// Add comments for clarity
// Handle form submission and API call
form.addEventListener('submit', async (e) => {
    // code
});
```

### CSS
```css
/* Use meaningful class names */
.form-group {
    margin-bottom: 20px;
}

/* Comment for complex selectors */
/* Responsive design for mobile devices */
@media (max-width: 768px) {
    /* styles */
}
```

## 🧪 Testing Your Changes

### Before Submitting PR
1. **Test Functionality**
   - Test new features thoroughly
   - Test in different browsers (Chrome, Firefox, Safari)
   - Test on mobile devices

2. **Test Database**
   ```bash
   # Check database changes via phpMyAdmin
   # http://localhost/phpmyadmin/
   ```

3. **Test API**
   ```bash
   # Test API endpoint with curl
   curl -X POST http://localhost/TinyLink/api/shorten.php \
     -H "Content-Type: application/json" \
     -d '{"url":"https://example.com"}'
   ```

4. **Check for Errors**
   - Open browser DevTools (F12)
   - Check Console tab for JavaScript errors
   - Check Network tab for HTTP errors

## 🐛 Reporting Issues

### Creating an Issue
1. Go to GitHub Issues
2. Click "New Issue"
3. Use descriptive title
4. Include steps to reproduce
5. Include error messages/screenshots

### Issue Title Format
```
[BUG] Short description
[FEATURE] Short description
[DOCS] Short description
```

## 🔐 Security Concerns

If you find a security vulnerability:
1. **Don't** create a public issue
2. Email the maintainer directly
3. Include detailed information
4. Allow time for a fix before disclosure

## 📚 Documentation

### Updating Docs
- Update relevant `.md` files
- Keep documentation current with code
- Include examples where helpful
- Use clear, concise language

### Doc File Structure
```
README.md                    - Main documentation
QUICKSTART.md               - Quick reference
CONFIGURATION.md            - Configuration options
INSTALLATION_GUIDE.md       - Setup instructions
CONTRIBUTING.md             - This file
```

## 🎯 Types of Contributions

### Feature Contributions
- New functionality
- Performance improvements
- UI/UX enhancements

**Discuss first:** Open an issue before starting major work

### Bug Fixes
- Critical bugs
- Security issues
- Logic errors

**Don't need discussion:** Bug fixes welcome anytime

### Documentation
- Better explanations
- Code examples
- Typo fixes
- New guides

**Always welcome:** Documentation improvements encouraged

## 📦 Project Structure

```
TinyLink/
├── api/
│   └── shorten.php         # URL shortening endpoint
├── assets/
│   ├── css/style.css       # Styling
│   └── js/app.js           # Frontend logic
├── config/
│   └── db.php              # Database config
├── index.php               # Main page
├── redirect.php            # Link handler
├── setup.php               # Database setup
└── [documentation files]
```

## 🔄 Git Workflow Summary

```bash
# 1. Fork on GitHub

# 2. Clone your fork
git clone https://github.com/YOUR_USERNAME/TinyLink.git
cd TinyLink

# 3. Create feature branch
git checkout -b feature/my-feature

# 4. Make changes and test

# 5. Commit changes
git add .
git commit -m "Add: my feature"

# 6. Push to your fork
git push origin feature/my-feature

# 7. Create Pull Request on GitHub

# 8. Wait for review and merge
```

## ✅ Checklist Before PR

- [ ] Code follows style guidelines
- [ ] Changes tested locally
- [ ] No console errors
- [ ] Database operations work correctly
- [ ] Documentation updated if needed
- [ ] Commit messages are clear
- [ ] No hardcoded credentials

## 🤝 Community Guidelines

- Be respectful and inclusive
- Help others learn
- Review PRs constructively
- Share knowledge and experience
- Give credit to contributors

## 📞 Getting Help

- **Documentation**: Check README.md and guides
- **Issues**: Search existing GitHub issues
- **Discussions**: Start a GitHub discussion
- **Email**: Contact the maintainer

## 🎉 Thank You!

Your contributions make TinyLink better for everyone. We appreciate your time and effort!

---

Happy coding! 🚀

For more information, see [README.md](README.md)
