<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Service;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RoutmouteDiscordApiService
{
    private const DISCORD_API = 'https://discord.com/api';

    private $botToken;
    private $httpClient;

    public function __construct(string $bot_token, HttpClientInterface $httpClient)
    {
        $this->botToken = $bot_token;
        $this->httpClient = $httpClient;
    }

    public function getUserFromDiscordId(string $discordId): array
    {
        $apiResponse = $this->sendToDiscordApi('GET', self::DISCORD_API . '/users/' . $discordId);

        $statusCode = $apiResponse->getStatusCode();
        if ($statusCode != 200)
        {
            if ($statusCode == 404) {
                throw new \Exception('User not exist', 404);
            }
            throw new ServiceUnavailableHttpException(null, 'Discord access failed.');
        }

        return $apiResponse->toArray();
    }

    public function getUserFromGuild(string $guildId, string $discordId)
    {
        return $this->sendToDiscordApi('GET', self::DISCORD_API . '/guilds/' . $guildId . '/members/' . $discordId)->toArray();
    }

    private function sendToDiscordApi(string $requestType, string $endPoint)
    {
        return $this->httpClient->request($requestType, $endPoint, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bot ".$this->botToken
            ]
        ]);
    }
}
