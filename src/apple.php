<?php

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

function verify_apple_id_token(string $idToken, string $clientId): array {
    $jwksJson = @file_get_contents('https://appleid.apple.com/auth/keys');
    if ($jwksJson === false) {
        throw new Exception('Unable to fetch Apple JWKS');
    }

    $jwks = json_decode($jwksJson, true);
    if (!is_array($jwks)) {
        throw new Exception('Invalid Apple JWKS');
    }

    $keys = JWK::parseKeySet($jwks);
    $decoded = (array) JWT::decode($idToken, $keys);

    $iss = $decoded['iss'] ?? null;
    if ($iss !== 'https://appleid.apple.com') {
        throw new Exception('Invalid issuer');
    }

    $aud = $decoded['aud'] ?? null;
    $audOk = is_array($aud) ? in_array($clientId, $aud, true) : $aud === $clientId;
    if (!$audOk) {
        throw new Exception('Invalid audience');
    }

    return $decoded;
}
