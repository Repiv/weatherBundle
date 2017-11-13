<?php

namespace NfqWeatherBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $root = $builder->root('nfq_weather');
        $root
            ->children()
                ->scalarNode('provider')
                    ->isRequired()
                    ->validate()
                    ->ifNotInArray(array('yahoo', 'openweathermap', 'delegating', 'cached'))
                        ->thenInvalid('Unknown weather provider %s')
                    ->end()
                ->end()
                ->arrayNode('providers')
                ->isRequired()
                ->children()
                    ->arrayNode('yahoo')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                        ->end()
                    ->end()
                    ->arrayNode('openweathermap')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                        ->end()
                    ->end()
                    ->arrayNode('delegating')
                        ->children()
                            ->arrayNode('providers')
                                ->prototype('scalar')
                                    ->validate()
                                    ->ifNotInArray(array('yahoo', 'openweathermap'))
                                        ->thenInvalid('Invalid delegating weather provider %s')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cached')
                        ->children()
                            ->scalarNode('provider')->isRequired()->end()
                            ->scalarNode('cache')
                                ->isRequired()
                                ->validate()
                                ->ifNotInArray(array('filesystem', 'array'))
                                    ->thenInvalid('Invalid cache %s')
                                ->end()
                            ->end()
                            ->scalarNode('ttl')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}