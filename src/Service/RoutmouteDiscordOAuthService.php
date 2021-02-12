<?php

namespace Routmoute\Bundle\DiscordBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class RoutmouteDiscordOAuthService
{
    private const DISCORD_AUTHORIZE_ENDPOINT = 'https://discord.com/api/oauth2/authorize';
    private const DISCORD_TOKEN_ENDPOINT = 'https://discord.com/api/oauth2/token';
    private const DISCORD_USER_DATA_ENDPONT = 'https://discord.com/api/users/@me';
    
    private $clientId;
    private $clientSecret;
    private $redirect_path;
    private $scope;
    private $urlGenerator;
    private $httpClient;
    private $csrfTokenManager;
    
    public function __construct(string $client_id, string $client_secret, string $redirect_path, string $scope, UrlGeneratorInterface $urlGenerator, HttpClientInterface $httpClient, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->redirect_path = $redirect_path;
        $this->scope = $scope;
        $this->urlGenerator = $urlGenerator;
        $this->httpClient = $httpClient;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function getRedirectDiscordUrl(): string
    {
        $queryParams = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->urlGenerator->generate($this->redirect_path, [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'scope' => $this->scope,
            'state' => $this->csrfTokenManager->getToken('routmoute_discord_auth')->getValue()
        ]);

        return self::DISCORD_AUTHORIZE_ENDPOINT . '?' . $queryParams;
    }

    public function getUserData(Request $request): array
    {
        $state = $request->get('state');

        if ($state && $this->csrfTokenManager->isTokenValid(new CsrfToken('routmoute_discord_auth', $state)))
        {
            $code = $request->get('code');
            if ($code)
            {
                return $this->getUserDataFromAccessToken($this->getAccessTokenFromCode($code));
            }
        }

        throw new InvalidCsrfTokenException();
    }
    
    private function getAccessTokenFromCode(string $code): string
    {
        $data = $this->httpClient->request('POST', self::DISCORD_TOKEN_ENDPOINT, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code'=> $code,
                'grant_type'=> 'authorization_code',
                'redirect_uri' => $this->urlGenerator->generate($this->redirect_path, [], UrlGeneratorInterface::ABSOLUTE_URL),
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
        $userData = $this->httpClient->request('GET', self::DISCORD_USER_DATA_ENDPONT, [
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