<?php

namespace Routmoute\Bundle\RoutmouteDiscordBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('routmoute_discord');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('oauth')
                    ->children()
                        ->scalarNode('client_id')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('client_secret')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('scope')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('api')
                    ->children()
                        ->scalarNode('bot_token')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}