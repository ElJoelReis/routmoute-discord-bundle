<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\Exception;

interface RoutmouteDiscordExceptionInterface extends \Throwable
{
    public function getReason(): string;
}
