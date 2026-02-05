<?php

namespace OAuth;

class MicrosoftProvider extends OAuthProvider {
    private $authUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    private $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private $userInfoUrl = 'https://graph.microsoft.com/v1.0/me';
    
    public function getProviderName() {
        return 'microsoft';
    }
    
    public function getAuthUrl($state) {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile User.Read',
            'state' => $state,
            'response_mode' => 'query'
        ];
        
        return $this->authUrl . '?' . http_build_query($params);
    }
    
    public function getAccessToken($code) {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
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
        
        // Get photo if available
        $avatar = '';
        try {
            $photoResponse = $this->httpRequest(
                'https://graph.microsoft.com/v1.0/me/photo/$value',
                'GET',
                null,
                ["Authorization: Bearer $accessToken"]
            );
            $avatar = 'data:image/jpeg;base64,' . base64_encode($photoResponse);
        } catch (\Exception $e) {
            // Photo might not be available
        }
        
        return [
            'id' => $data['id'] ?? '',
            'email' => $data['mail'] ?? $data['userPrincipalName'] ?? '',
            'name' => $data['displayName'] ?? '',
            'avatar' => $avatar,
            'raw' => $data
        ];
    }
}
