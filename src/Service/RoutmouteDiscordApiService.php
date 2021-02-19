<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Service;

use Routmoute\Bundle\RoutmouteDiscordBundle\Exception\DiscordAccessFailedException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
            throw new DiscordAccessFailedException();
        }

        return $apiResponse->toArray();
    }

    public function getUserFromGuild(string $guildId, string $discordId): array
    {
        $apiResponse = $this->sendToDiscordApi('GET', self::DISCORD_API . '/guilds/' . $guildId . '/members/' . $discordId);

        $statusCode = $apiResponse->getStatusCode();
        if ($statusCode != 200)
        {
            if ($statusCode == 404) {
                throw new \Exception('User or guild not exist', 404);
            }
            throw new DiscordAccessFailedException();
        }
        
        return $apiResponse->toArray();
    }

    public function getAllUsersFromGuild(string $guildId): array
    {
        $apiResponse = $this->sendToDiscordApi('GET', self::DISCORD_API . '/guilds/' . $guildId . '/members');
        $statusCode = $apiResponse->getStatusCode();
        if ($statusCode != 200)
        {
            if ($statusCode == 404) {
                throw new \Exception('Guild not exist', 404);
            }
            throw new DiscordAccessFailedException();
        }
        return $apiResponse->toArray();
    }

    private function sendToDiscordApi(string $requestType, string $endPoint): ResponseInterface
    {
        return $this->httpClient->request($requestType, $endPoint, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bot ".$this->botToken
            ]
        ]);
    }
}
