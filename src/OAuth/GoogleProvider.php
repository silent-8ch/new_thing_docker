<?php

namespace OAuth;

class GoogleProvider extends OAuthProvider {
    private $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    private $tokenUrl = 'https://oauth2.googleapis.com/token';
    private $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    public function getProviderName() {
        return 'google';
    }
    
    public function getAuthUrl($state) {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online'
        ];
        
        return $this->authUrl . '?' . http_build_query($params);
    }
    
    public function getAccessToken($code) {
        $data = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $response = $this->httpRequest($this->tokenUrl, 'POST', http_build_query($data), [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $result = json_decode($response, true);
        
        if (!isset($result['access_token'])) {
            throw new \Exception('Failed to get access token');
        }
        
        return $result['access_token'];
    }
    
    public function getUserInfo($accessToken) {
        $response = $this->httpRequest($this->userInfoUrl, 'GET', null, [
            "Authorization: Bearer $accessToken"
        ]);
        
        $data = json_decode($response, true);
        
        return [
            'id' => $data['id'] ?? '',
            'email' => $data['email'] ?? '',
            'name' => $data['name'] ?? '',
            'avatar' => $data['picture'] ?? '',
            'raw' => $data
        ];
    }
}
