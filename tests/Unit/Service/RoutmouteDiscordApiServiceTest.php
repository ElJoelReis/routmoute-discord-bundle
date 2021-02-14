<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Routmoute\Bundle\RoutmouteDiscordBundle\Service\RoutmouteDiscordApiService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RoutmouteDiscordApiServiceTest extends TestCase
{
    private $botToken;
    private $discordId;
    private $guildId;

    public function testGetUserFromDiscordId()
    {
        $this->botToken = $this->generateRandomString();
        $this->discordId = $this->generateRandomString();

        $mockHttpClient = new MockHttpClient(function($method, $url, $options) {
            $this->assertEquals("GET", $method);
            $this->assertEquals('https://discord.com/api/users/' . $this->discordId, $url);
            $this->assertEquals($options['headers'][1], "Authorization: Bot " . $this->botToken);

            return new MockResponse(json_encode([]), ['headers' => ['Content-Type' => 'application/json']]);
        });

        $apiService = new RoutmouteDiscordApiService($this->botToken, $mockHttpClient);

        $apiService->getUserFromDiscordId($this->discordId);
    }

    public function testGetUserFromGuild()
    {
        $this->botToken = $this->generateRandomString();
        $this->discordId = $this->generateRandomString();
        $this->guildId = $this->generateRandomString();

        $mockHttpClient = new MockHttpClient(function($method, $url, $options) {
            $this->assertEquals("GET", $method);
            $this->assertEquals("https://discord.com/api/guilds/" . $this->guildId . "/members/" . $this->discordId, $url);
            $this->assertEquals($options['headers'][1], "Authorization: Bot " . $this->botToken);

            return new MockResponse(json_encode([]), ['headers' => ['Content-Type' => 'application/json']]);
        });

        $apiService = new RoutmouteDiscordApiService($this->botToken, $mockHttpClient);

        $apiService->getUserFromGuild($this->guildId, $this->discordId);
    }

    private function generateRandomString($length = 50) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
