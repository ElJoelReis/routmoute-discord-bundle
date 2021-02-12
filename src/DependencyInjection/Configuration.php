<?php

namespace Routmoute\Bundle\DiscordBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('routmoute_discord');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('client_id')
                    ->defaultValue('%env(ROUTMOUTE_DISCORD_CLIENT_ID)%')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('client_secret')
                    ->defaultValue('%env(ROUTMOUTE_DISCORD_CLIENT_SECRET)%')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('redirect_path')
                    ->defaultValue('routmoute_discord_receiver')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('scope')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->scalarPrototype()
                        ->defaultValue(["identify"])
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}