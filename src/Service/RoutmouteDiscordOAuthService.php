<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RoutmouteDiscordOAuthService
{
    private const DISCORD_API = 'https://discord.com/api';
    
    private $clientId;
    private $clientSecret;
    private $scope;
    private $httpClient;
    private $csrfTokenManager;
    
    public function __construct(string $client_id, string $client_secret, string $scope, HttpClientInterface $httpClient, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->scope = $scope;
        $this->httpClient = $httpClient;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function getRedirectDiscordUrl(string $redirectUrl): string
    {
        $csrfToken = $this->csrfTokenManager->getToken('routmoute_discord_auth')->getValue();

        $queryParams = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $this->scope,
            'state' => $csrfToken
        ]);

        return self::DISCORD_API . '/oauth2/authorize?' . $queryParams;
    }

    public function getUserData(Request $request, string $redirectUrl): array
    {
        $state = $request->get('state');

        if ($state && $this->csrfTokenManager->isTokenValid(new CsrfToken('routmoute_discord_auth', $state)))
        {
            $code = $request->get('code');
            if ($code)
            {
                return $this->getUserDataFromAccessToken($this->getAccessTokenFromCode($code, $redirectUrl));
            }
        }

        throw new InvalidCsrfTokenException();
    }
    
    private function getAccessTokenFromCode(string $code, string $redirectUrl): string
    {
        $data = $this->httpClient->request('POST', self::DISCORD_API . '/oauth2/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code'=> $code,
                'grant_type'=> 'authorization_code',
                'redirect_uri' => $redirectUrl,
                'scope' => $this->scope,
            ]
        ])->toArray();

        if (!$data['access_token']) {
            throw new ServiceUnavailableHttpException(null, 'Discord access failed.');
        }

        return $data['access_token'];
    }

    private function getUserDataFromAccessToken(string $accessToken): array
    {
        $userData = $this->httpClient->request('GET', self::DISCORD_API . "/users/@me", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$accessToken}"
            ]
        ])->toArray();

        if (!$userData['id'])
        {
            throw new ServiceUnavailableHttpException(null, 'Discord user unattainable.');
        }

        return $userData;
    }
}