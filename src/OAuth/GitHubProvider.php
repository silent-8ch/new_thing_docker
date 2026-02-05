<?php

namespace OAuth;

class GitHubProvider extends OAuthProvider {
    private $authUrl = 'https://github.com/login/oauth/authorize';
    private $tokenUrl = 'https://github.com/login/oauth/access_token';
    private $userInfoUrl = 'https://api.github.com/user';
    
    public function getProviderName() {
        return 'github';
    }
    
    public function getAuthUrl($state) {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'read:user user:email',
            'state' => $state
        ];
        
        return $this->authUrl . '?' . http_build_query($params);
    }
    
    public function getAccessToken($code) {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        ];
        
        $response = $this->httpRequest($this->tokenUrl, 'POST', http_build_query($data), [
            'Accept: application/json',
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
            "Authorization: Bearer $accessToken",
            "User-Agent: OAuth-Playground"
        ]);
        
        $data = json_decode($response, true);
        
        // Get primary email if not public
        $email = $data['email'];
        if (empty($email)) {
            $emailResponse = $this->httpRequest('https://api.github.com/user/emails', 'GET', null, [
                "Authorization: Bearer $accessToken",
                "User-Agent: OAuth-Playground"
            ]);
            $emails = json_decode($emailResponse, true);
            foreach ($emails as $emailData) {
                if ($emailData['primary']) {
                    $email = $emailData['email'];
                    break;
                }
            }
        }
        
        return [
            'id' => (string)$data['id'],
            'email' => $email ?? '',
            'name' => $data['name'] ?? $data['login'],
            'avatar' => $data['avatar_url'] ?? '',
            'raw' => $data
        ];
    }
}
