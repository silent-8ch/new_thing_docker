<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/OAuth/OAuthProvider.php';
require_once __DIR__ . '/../src/OAuth/GoogleProvider.php';
require_once __DIR__ . '/../src/OAuth/GitHubProvider.php';
require_once __DIR__ . '/../src/OAuth/MicrosoftProvider.php';

initSession();

// Get parameters
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$provider = $_GET['provider'] ?? '';
$error = $_GET['error'] ?? '';

// Handle OAuth errors
if ($error) {
    die('OAuth Error: ' . htmlspecialchars($error));
}

// Verify state to prevent CSRF attacks
if (empty($state) || empty($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
    die('Invalid state parameter. Possible CSRF attack.');
}

// Verify provider matches
if (empty($provider) || empty($_SESSION['oauth_provider']) || $provider !== $_SESSION['oauth_provider']) {
    die('Provider mismatch.');
}

// Create provider instance
$oauthProvider = null;
switch ($provider) {
    case 'google':
        $redirectUri = APP_URL . '/callback.php?provider=google';
        $oauthProvider = new \OAuth\GoogleProvider(GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, $redirectUri);
        break;
        
    case 'github':
        $redirectUri = APP_URL . '/callback.php?provider=github';
        $oauthProvider = new \OAuth\GitHubProvider(GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET, $redirectUri);
        break;
        
    case 'microsoft':
        $redirectUri = APP_URL . '/callback.php?provider=microsoft';
        $oauthProvider = new \OAuth\MicrosoftProvider(MICROSOFT_CLIENT_ID, MICROSOFT_CLIENT_SECRET, $redirectUri);
        break;
        
    default:
        die('Invalid provider');
}

try {
    // Exchange code for access token
    $accessToken = $oauthProvider->getAccessToken($code);
    
    // Get user information
    $userInfo = $oauthProvider->getUserInfo($accessToken);
    
    // Save or update user in database
    $userId = $oauthProvider->saveOrUpdateUser($userInfo);
    
    // Set session
    $_SESSION['user_id'] = $userId;
    
    // Clean up OAuth session data
    unset($_SESSION['oauth_state']);
    unset($_SESSION['oauth_provider']);
    
    // Redirect to dashboard
    redirect('/');
    
} catch (Exception $e) {
    die('Authentication failed: ' . htmlspecialchars($e->getMessage()));
}
