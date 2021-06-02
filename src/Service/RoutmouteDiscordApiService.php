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

    public function getAllUsersFromGuild(string $guildId, int $limit = 1, int $after = 0): array
    {
        $apiResponse = $this->sendToDiscordApi('GET', self::DISCORD_API . '/guilds/' . $guildId . '/members?limit=' . $limit . '&after=' . $after);
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

    /**
     *
     * @param string $channelId
     * @param integer $limit default 50
     * @param string|null $around Message Id
     * @param string|null $before Message Id
     * @param string|null $after Message Id
     * @return array of messages
     */
    public function getChannelMessages(string $channelId, int $limit = 50, ?string $around = null, ?string $before = null, ?string $after = null): array
    {
        $addOptions = "?limit=" . $limit;
        if ($around) $addOptions += "&around=" . $around;
        if ($before) $addOptions += "&before=" . $before;
        if ($before) $addOptions += "&after=" . $after;
        $apiResponse = $this->sendToDiscordApi('GET', self::DISCORD_API . '/channels/' . $channelId . '/messages' . $addOptions);
        $statusCode = $apiResponse->getStatusCode();
        if ($statusCode != 200)
        {
            if ($statusCode == 404) {
                throw new \Exception('Channel not exist', 404);
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
