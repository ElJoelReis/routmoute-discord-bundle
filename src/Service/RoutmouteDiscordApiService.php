<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class RoutmouteDiscordApiService
{
    private const DISCORD_USER_DATA_ENDPONT = 'https://discord.com/api/users';

    private $botToken;

    public function __construct(string $bot_token)
    {
        $this->botToken = $bot_token;
    }

    public function getUserFromDiscordId(string $discordId): array
    {
        $apiResponse = (new HttpClient())->create()->request('GET', self::DISCORD_USER_DATA_ENDPONT."/$discordId", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bot ".$this->botToken
            ]
        ]);

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
}
