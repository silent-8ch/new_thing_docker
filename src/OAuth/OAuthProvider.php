<?php

namespace OAuth;

abstract class OAuthProvider {
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    
    public function __construct($clientId, $clientSecret, $redirectUri) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }
    
    abstract public function getAuthUrl($state);
    abstract public function getAccessToken($code);
    abstract public function getUserInfo($accessToken);
    abstract public function getProviderName();
    
    protected function httpRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = $data;
            }
        }
        
        if (!empty($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new \Exception("HTTP Error $httpCode: $response");
        }
        
        return $response;
    }
    
    public function saveOrUpdateUser($userInfo) {
        $db = getDatabase();
        
        // Check if user exists
        $stmt = $db->prepare('SELECT id FROM users WHERE provider = ? AND provider_user_id = ?');
        $stmt->execute([$this->getProviderName(), $userInfo['id']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing user
            $stmt = $db->prepare('
                UPDATE users 
                SET email = ?, name = ?, avatar = ?, token_claims = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $userInfo['email'] ?? null,
                $userInfo['name'] ?? null,
                $userInfo['avatar'] ?? null,
                json_encode($userInfo),
                $existing['id']
            ]);
            return $existing['id'];
        } else {
            // Insert new user
            $stmt = $db->prepare('
                INSERT INTO users (provider, provider_user_id, email, name, avatar, token_claims)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $this->getProviderName(),
                $userInfo['id'],
                $userInfo['email'] ?? null,
                $userInfo['name'] ?? null,
                $userInfo['avatar'] ?? null,
                json_encode($userInfo)
            ]);
            return $db->lastInsertId();
        }
    }
}
