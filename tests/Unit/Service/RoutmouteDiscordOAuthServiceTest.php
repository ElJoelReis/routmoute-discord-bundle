<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Routmoute\Bundle\RoutmouteDiscordBundle\Service\RoutmouteDiscordOAuthService;

class RoutmouteDiscordOAuthServiceTest extends TestCase
{
    public function testGetRedirectDiscordUrl() {
        $mockHttpClient = new MockHttpClient();

        $csrfTokenMock = $this->getMockBuilder(CsrfToken::class)->disableOriginalConstructor()->getMock();
        $csrfTokenMock
            ->expects($this->once())
            ->method('getValue')
        ;

        $csrfTokenManagerMock = $this->getMockBuilder(CsrfTokenManagerInterface::class)->getMock();
        $csrfTokenManagerMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($csrfTokenMock)
        ;

        $client_id = $this->generateRandomString();
        $client_secret = $this->generateRandomString();
        $scope = "identify email";

        $oAuthService = new RoutmouteDiscordOAuthService(
            $client_id,
            $client_secret,
            $scope,
            $mockHttpClient,
            $csrfTokenManagerMock
        );

        $dicordRedirectUri = $this->generateRandomString();

        $this->assertEquals($oAuthService->getRedirectDiscordUrl($dicordRedirectUri), "https://discord.com/api/oauth2/authorize?client_id=" . $client_id . "&redirect_uri=" . urlencode($dicordRedirectUri) . "&response_type=code&scope=" . urlencode($scope));
    }

    private function generateRandomString($length = 50) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
