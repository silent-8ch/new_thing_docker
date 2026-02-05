<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/OAuth/OAuthProvider.php';
require_once __DIR__ . '/../src/OAuth/GoogleProvider.php';
require_once __DIR__ . '/../src/OAuth/GitHubProvider.php';
require_once __DIR__ . '/../src/OAuth/MicrosoftProvider.php';

initSession();

$provider = $_GET['provider'] ?? '';

// Create provider instance
$oauthProvider = null;
switch ($provider) {
    case 'google':
        if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
            die('Google OAuth is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env file.');
        }
        $redirectUri = APP_URL . '/callback.php?provider=google';
        $oauthProvider = new \OAuth\GoogleProvider(GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, $redirectUri);
        break;
        
    case 'github':
        if (empty(GITHUB_CLIENT_ID) || empty(GITHUB_CLIENT_SECRET)) {
            die('GitHub OAuth is not configured. Please set GITHUB_CLIENT_ID and GITHUB_CLIENT_SECRET in .env file.');
        }
        $redirectUri = APP_URL . '/callback.php?provider=github';
        $oauthProvider = new \OAuth\GitHubProvider(GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET, $redirectUri);
        break;
        
    case 'microsoft':
        if (empty(MICROSOFT_CLIENT_ID) || empty(MICROSOFT_CLIENT_SECRET)) {
            die('Microsoft OAuth is not configured. Please set MICROSOFT_CLIENT_ID and MICROSOFT_CLIENT_SECRET in .env file.');
        }
        $redirectUri = APP_URL . '/callback.php?provider=microsoft';
        $oauthProvider = new \OAuth\MicrosoftProvider(MICROSOFT_CLIENT_ID, MICROSOFT_CLIENT_SECRET, $redirectUri);
        break;
        
    default:
        die('Invalid provider');
}

// Generate state for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = $provider;

// Redirect to provider's authorization page
$authUrl = $oauthProvider->getAuthUrl($state);
redirect($authUrl);
