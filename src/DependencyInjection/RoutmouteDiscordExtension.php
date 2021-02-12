<?php

namespace Routmoute\Bundle\DiscordBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoutmouteDiscordExtension extends Extension {
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $scope = '';
        foreach($config['scope'] as $value) {
            if ($scope != '') {
                $scope .= ' ';
            }
            $scope .= $value;
        }

        $container->setParameter('routmoute_discord.client_id', $config['client_id']);
        $container->setParameter('routmoute_discord.client_secret', $config['client_secret']);
        $container->setParameter('routmoute_discord.redirect_path', $config['redirect_path']);
        $container->setParameter('routmoute_discord.scope', $scope);
    }
}
