<?php

namespace NfqWeatherBundle\Provider;

use NfqWeatherBundle\Location;
use NfqWeatherBundle\Weather;

class DelegatingWeatherProvider implements WeatherProviderInterface
{
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function fetch(Location $location): Weather
    {
        foreach($this->providers as $provider) {
            try {
                return $provider->fetch($location);
            } catch(WeatherProviderException $e) {
                // Lets try another one
            }
        }

        throw New WeatherProviderException('None of the given privers were able to fetch weather for location');
    }
}