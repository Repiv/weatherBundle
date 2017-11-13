<?php

namespace NfqWeatherBundle\DependencyInjection;

use NfqWeatherBundle\Provider\YahooWeatherProvider;
use NfqWeatherBundle\Provider\OpenWeatherProvider;
use NfqWeatherBundle\Provider\DelegatingWeatherProvider;
use NfqWeatherBundle\Provider\CachedWeatherProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class NfqWeatherExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $definitionId = 'nfq.weather';

        if($config['provider'] === 'yahoo') {
            $this->getYahooDefintion($container, $definitionId);
        }

        if($config['provider'] === 'openweathermap') {
            $this->getOpenWeatherMapDefinition($container, $definitionId, $config);
        }

        if($config['provider'] === 'delegating') {
            $this->getDelegatingDefinition($container, $definitionId, $config);
        }

        if($config['provider'] === 'cached') {
            switch($config['providers']['cached']['cache']) {
                case 'filesystem':
                    $cache = $container->setDefinition('nfq.weather.cache', new Definition(FilesystemAdapter::class));
                    break;
                case 'array':
                    $cache = $container->setDefinition('nfq.weather.cache', new Definition(ArrayAdapter::class));
                    break;
                default:
                    $cache = $container->setDefinition('nfq.weather.cache', new Definition(FilesystemAdapter::class));
            }

            if($config['providers']['cached']['provider'] === 'delegating') {
                $provider = $this->getDelegatingDefinition($container, $definitionId, $config);
            } elseif($config['providers']['cached']['provider'] === 'openweathermap') {
                $provider = $this->getOpenWeatherMapDefinition($container, $definitionId, $config);
            } elseif($config['providers']['cached']['provider'] === 'yahoo') {
                $provider = $this->getYahooDefintion($container, $definitionId);
            }

            $container
                ->setDefinition(
                    $definitionId,
                    new Definition(CachedWeatherProvider::class,
                    [$cache, $provider, $config['providers']['cached']['ttl']]
                ));
        }
    }

    private function getYahooDefintion($container, $definitionId)
    {
        return $container->setDefinition($definitionId, new Definition(YahooWeatherProvider::class));
    }

    private function getOpenWeatherMapDefinition($container, $definitionId, $config)
    {
        return $container
            ->setDefinition($definitionId, new Definition(OpenWeatherProvider::class))
            ->addMethodCall('setKey', array($config['providers']['openweathermap']['api_key']));
    }

    private function getDelegatingDefinition($container, $definitionId, $config)
    {
        $providers = [];

        foreach($config['providers']['delegating']['providers'] as $provider) {
            if($provider === 'yahoo') {
                $providers[] = $this->getYahooDefintion($container, $definitionId);
            }

            if($provider === 'openweathermap') {
                $providers[] = $this->getOpenWeatherMapDefinition($container, $definitionId, $config);
            }
        }

        return $container->setDefinition(
            $definitionId,
            new Definition(DelegatingWeatherProvider::class,
                [$providers]
            ));
    }
}