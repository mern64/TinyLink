/**
 * TinyLink URL Shortener - Frontend Application
 * Handles API communication and UI interactions
 */

// Get DOM elements
const shortenForm = document.getElementById('shortenForm');
const urlInput = document.getElementById('urlInput');
const submitBtn = document.getElementById('submitBtn');
const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const successState = document.getElementById('successState');
const errorMessage = document.getElementById('errorMessage');
const shortUrlInput = document.getElementById('shortUrl');
const originalUrlDisplay = document.getElementById('originalUrl');

/**
 * Handle form submission
 * Sends the URL to the backend API for shortening
 */
shortenForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const url = urlInput.value.trim();

    // Validate URL
    if (!url) {
        showError('Please enter a URL');
        return;
    }

    if (!isValidUrl(url)) {
        showError('Please enter a valid URL (must start with http:// or https://)');
        return;
    }

    // Show loading state
    showLoading();

    try {
        // Call API endpoint
        const response = await fetch('api/shorten.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ url: url }),
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(data);
        } else {
            showError(data.message || 'Failed to shorten URL');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    }
});

/**
 * Validate URL format
 * @param {string} url - URL to validate
 * @returns {boolean} - True if URL is valid
 */
function isValidUrl(url) {
    try {
        new URL(url);
        return url.startsWith('http://') || url.startsWith('https://');
    } catch {
        return false;
    }
}

/**
 * Show loading state
 * Hide form and error/success states, display loading spinner
 */
function showLoading() {
    shortenForm.style.display = 'none';
    errorState.style.display = 'none';
    successState.style.display = 'none';
    loadingState.style.display = 'block';
    submitBtn.disabled = true;
}

/**
 * Show error state
 * Display error message and allow user to try again
 * @param {string} message - Error message to display
 */
function showError(message) {
    shortenForm.style.display = 'block';
    loadingState.style.display = 'none';
    successState.style.display = 'none';
    errorState.style.display = 'block';
    errorMessage.textContent = message;
    submitBtn.disabled = false;

    // Auto-scroll to error message
    errorState.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Show success state
 * Display shortened URL and allow user to copy and shorten more
 * @param {object} data - Response data from API containing short code and URL
 */
function showSuccess(data) {
    shortenForm.style.display = 'none';
    loadingState.style.display = 'none';
    errorState.style.display = 'none';
    successState.style.display = 'block';

    // Populate success data
    originalUrlDisplay.textContent = data.original_url;
    shortUrlInput.value = data.short_url;

    submitBtn.disabled = false;

    // Auto-scroll to success message
    successState.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Reset form and clear all states
 * Allow user to shorten another URL
 */
function resetForm() {
    shortenForm.style.display = 'block';
    loadingState.style.display = 'none';
    errorState.style.display = 'none';
    successState.style.display = 'none';
    urlInput.value = '';
    errorMessage.textContent = '';
    shortUrlInput.value = '';
    originalUrlDisplay.textContent = '';
    submitBtn.disabled = false;

    // Focus on input
    urlInput.focus();

    // Auto-scroll to form
    shortenForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Copy short URL to clipboard
 * Shows user feedback after copying
 */
function copyToClipboard() {
    const shortUrl = shortUrlInput.value;

    if (!shortUrl) {
        alert('No URL to copy');
        return;
    }

    // Use modern clipboard API if available
    if (navigator.clipboard) {
        navigator.clipboard.writeText(shortUrl).then(() => {
            showCopyNotification();
        }).catch(err => {
            console.error('Failed to copy:', err);
            fallbackCopyToClipboard(shortUrl);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyToClipboard(shortUrl);
    }
}

/**
 * Fallback method to copy text to clipboard
 * For browsers that don't support navigator.clipboard
 * @param {string} text - Text to copy
 */
function fallbackCopyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showCopyNotification();
}

/**
 * Show copy notification
 * Temporarily displays a message indicating successful copy
 */
function showCopyNotification() {
    const btn = event.target;
    const originalText = btn.textContent;

    btn.textContent = '✓ Copied!';
    btn.style.background = '#38a169';

    setTimeout(() => {
        btn.textContent = originalText;
        btn.style.background = '';
    }, 2000);
}

/**
 * Initialize - Focus on input field on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    urlInput.focus();
});
