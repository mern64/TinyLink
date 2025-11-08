<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TinyLink - URL Shortener</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1 class="logo">🔗 TinyLink</h1>
                <p class="tagline">Simplify Your Links, Track Your Clicks</p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- URL Shortener Form -->
            <section class="shortener-section">
                <div class="card">
                    <h2>Shorten Your URL</h2>
                    <form id="shortenForm" class="form">
                        <div class="form-group">
                            <label for="urlInput">Enter Your Long URL</label>
                            <input 
                                type="url" 
                                id="urlInput" 
                                name="url" 
                                placeholder="https://example.com/very/long/url" 
                                required
                            >
                            <small>Paste any URL and we'll shorten it for you</small>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span>Shorten URL</span>
                        </button>
                    </form>

                    <!-- Loading State -->
                    <div id="loadingState" class="loading-state" style="display: none;">
                        <div class="spinner"></div>
                        <p>Creating your short link...</p>
                    </div>

                    <!-- Error State -->
                    <div id="errorState" class="error-state" style="display: none;">
                        <p id="errorMessage"></p>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">Try Again</button>
                    </div>

                    <!-- Success State -->
                    <div id="successState" class="success-state" style="display: none;">
                        <div class="success-content">
                            <div class="success-icon">✓</div>
                            <h3>Link Shortened Successfully!</h3>
                            
                            <div class="url-display">
                                <div class="url-item">
                                    <label>Original URL</label>
                                    <p id="originalUrl" class="url-text"></p>
                                </div>
                                
                                <div class="url-item">
                                    <label>Short URL</label>
                                    <div class="short-url-container">
                                        <input 
                                            type="text" 
                                            id="shortUrl" 
                                            class="url-input" 
                                            readonly
                                        >
                                        <button type="button" class="btn btn-copy" onclick="copyToClipboard()">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="button-group">
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    Shorten Another
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="features-section">
                <h2>Why Choose TinyLink?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Instant Shortening</h3>
                        <p>Generate short, unique codes instantly using our Base62 algorithm</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Click Tracking</h3>
                        <p>Monitor how many times your links are clicked in real-time</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure & Reliable</h3>
                        <p>Your URLs are stored safely in our database with automatic timestamping</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Mobile Friendly</h3>
                        <p>Works seamlessly on all devices and browsers</p>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section class="how-it-works">
                <h2>How It Works</h2>
                <div class="steps-container">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h4>Paste URL</h4>
                        <p>Enter your long URL in the form above</p>
                    </div>

                    <div class="step-arrow">→</div>

                    <div class="step">
                        <div class="step-number">2</div>
                        <h4>Generate Code</h4>
                        <p>We create a unique short code for your link</p>
                    </div>

                    <div class="step-arrow">→</div>

                    <div class="step">
                        <div class="step-number">3</div>
                        <h4>Share & Track</h4>
                        <p>Share the short link and monitor its performance</p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 TinyLink - Making Links Better</p>
        </footer>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
