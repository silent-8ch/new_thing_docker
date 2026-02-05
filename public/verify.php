<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/db.php';

use Google\Client as GoogleClient;

header('Content-Type: application/json');
session_start();

$raw = file_get_contents('php://input');
$id_token = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // either form-encoded or raw; support both
    if (!empty($_POST['id_token'])) {
        $id_token = $_POST['id_token'];
    } elseif (!empty($_POST['credential'])) {
        $id_token = $_POST['credential'];
    } else {
        parse_str($raw, $parsed);
        if (!empty($parsed['id_token'])) $id_token = $parsed['id_token'];
        if (empty($id_token) && !empty($parsed['credential'])) $id_token = $parsed['credential'];
        // or JSON body:
        $json = json_decode($raw, true);
        if (empty($id_token) && !empty($json['id_token'])) $id_token = $json['id_token'];
        if (empty($id_token) && !empty($json['credential'])) $id_token = $json['credential'];
    }
}

if (!$id_token) {
    http_response_code(400);
    echo json_encode(['error' => 'id_token required']);
    exit;
}

$googleClientId = getenv('GOOGLE_CLIENT_ID') ?: null;
if (!$googleClientId) {
    http_response_code(500);
    echo json_encode(['error' => 'GOOGLE_CLIENT_ID not set on server']);
    exit;
}

$gclient = new GoogleClient(['client_id' => $googleClientId]);
$payload = $gclient->verifyIdToken($id_token);

if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid ID token']);
    exit;
}

// payload contains standard ID token fields
$sub = $payload['sub'] ?? null;
$email = $payload['email'] ?? null;
$email_verified = $payload['email_verified'] ?? null;
$name = $payload['name'] ?? null;
$given_name = $payload['given_name'] ?? null;
$family_name = $payload['family_name'] ?? null;
$picture = $payload['picture'] ?? null;
$locale = $payload['locale'] ?? null;
$hd = $payload['hd'] ?? null;
$iss = $payload['iss'] ?? null;
$azp = $payload['azp'] ?? null;
$aud = $payload['aud'] ?? null;
$nbf = $payload['nbf'] ?? null;
$iat = $payload['iat'] ?? null;
$exp = $payload['exp'] ?? null;
$jti = $payload['jti'] ?? null;

if (!$sub || !$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields in token payload']);
    exit;
}

try {
    $pdo = get_db_pdo();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE google_id = :google_id LIMIT 1");
    $stmt->execute([':google_id' => $sub]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE users SET
            iss = :iss, azp = :azp, aud = :aud, email = :email, email_verified = :email_verified,
            name = :name, given_name = :given_name, family_name = :family_name,
            picture = :picture, locale = :locale, hd = :hd, nbf = :nbf, iat = :iat, exp = :exp, jti = :jti,
            updated_at = NOW()
            WHERE google_id = :google_id");
        $stmt->execute([
            ':google_id' => $sub,
            ':iss' => $iss,
            ':azp' => $azp,
            ':aud' => $aud,
            ':email' => $email,
            ':email_verified' => is_null($email_verified) ? null : (int) $email_verified,
            ':name' => $name,
            ':given_name' => $given_name,
            ':family_name' => $family_name,
            ':picture' => $picture,
            ':locale' => $locale,
            ':hd' => $hd,
            ':nbf' => $nbf,
            ':iat' => $iat,
            ':exp' => $exp,
            ':jti' => $jti
        ]);

        $_SESSION['user'] = [
            'google_id' => $sub,
            'email' => $email,
            'name' => $name
        ];
    }

    echo json_encode([
        'ok' => true,
        'exists' => (bool) $existing,
        'user' => [
            'google_id' => $sub,
            'iss' => $iss,
            'azp' => $azp,
            'aud' => $aud,
            'email' => $email,
            'email_verified' => $email_verified,
            'name' => $name,
            'given_name' => $given_name,
            'family_name' => $family_name,
            'picture' => $picture,
            'locale' => $locale,
            'hd' => $hd,
            'nbf' => $nbf,
            'iat' => $iat,
            'exp' => $exp,
            'jti' => $jti
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error', 'detail' => $e->getMessage()]);
}