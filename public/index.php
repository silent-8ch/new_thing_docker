<?php
require_once __DIR__ . '/../src/config.php';

initSession();

// If logged in, show dashboard; otherwise show login page
if (isLoggedIn()) {
    $user = getUser();
    
    ob_start();
    include __DIR__ . '/../src/Views/dashboard.php';
    $content = ob_get_clean();
    
    $title = 'Dashboard - OAuth Playground';
    include __DIR__ . '/../src/Views/layout.php';
} else {
    ob_start();
    include __DIR__ . '/../src/Views/login.php';
    $content = ob_get_clean();
    
    $title = 'Login - OAuth Playground';
    include __DIR__ . '/../src/Views/layout.php';
}
