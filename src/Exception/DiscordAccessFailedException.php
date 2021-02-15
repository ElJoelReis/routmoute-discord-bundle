<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Exception;

final class DiscordAccessFailedException extends \Exception implements RoutmouteDiscordExceptionInterface
{
    public function getReason(): string
    {
        return 'Discord access failed.';
    }
}
