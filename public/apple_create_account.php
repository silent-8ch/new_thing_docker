<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/apple.php';

header('Content-Type: application/json');
session_start();

$raw = file_get_contents('php://input');
$id_token = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id_token'])) {
        $id_token = $_POST['id_token'];
    } else {
        parse_str($raw, $parsed);
        if (!empty($parsed['id_token'])) $id_token = $parsed['id_token'];
        $json = json_decode($raw, true);
        if (empty($id_token) && !empty($json['id_token'])) $id_token = $json['id_token'];
    }
}

if (!$id_token) {
    http_response_code(400);
    echo json_encode(['error' => 'id_token required']);
    exit;
}

$appleClientId = getenv('APPLE_CLIENT_ID') ?: null;
if (!$appleClientId) {
    http_response_code(500);
    echo json_encode(['error' => 'APPLE_CLIENT_ID not set on server']);
    exit;
}

try {
    $payload = verify_apple_id_token($id_token, $appleClientId);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid ID token', 'detail' => $e->getMessage()]);
    exit;
}

$sub = $payload['sub'] ?? null;
$email = $payload['email'] ?? null;
$email_verified = $payload['email_verified'] ?? null;
$is_private_email = $payload['is_private_email'] ?? null;
$iss = $payload['iss'] ?? null;
$aud = $payload['aud'] ?? null;
$iat = $payload['iat'] ?? null;
$exp = $payload['exp'] ?? null;

if (!$sub) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing sub in token payload']);
    exit;
}

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Email not available from Apple']);
    exit;
}

try {
    $pdo = get_db_pdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE apple_id = :apple_id LIMIT 1');
    $stmt->execute([':apple_id' => $sub]);
    $existing = $stmt->fetch();

    if ($existing) {
        http_response_code(409);
        echo json_encode(['error' => 'Account already exists']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO users (
            apple_id, apple_email, apple_email_verified, apple_is_private_email,
            iss, aud, iat, exp, created_at, updated_at
        ) VALUES (
            :apple_id, :apple_email, :apple_email_verified, :apple_is_private_email,
            :iss, :aud, :iat, :exp, NOW(), NOW()
        )');
    $stmt->execute([
        ':apple_id' => $sub,
        ':apple_email' => $email,
        ':apple_email_verified' => is_null($email_verified) ? null : (int) ($email_verified === true || $email_verified === 'true'),
        ':apple_is_private_email' => is_null($is_private_email) ? null : (int) ($is_private_email === true || $is_private_email === 'true'),
        ':iss' => $iss,
        ':aud' => is_array($aud) ? json_encode($aud) : $aud,
        ':iat' => $iat,
        ':exp' => $exp
    ]);

    $_SESSION['user'] = [
        'provider' => 'apple',
        'apple_id' => $sub,
        'email' => $email,
        'name' => null
    ];

    echo json_encode(['ok' => true, 'created' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error', 'detail' => $e->getMessage()]);
}
